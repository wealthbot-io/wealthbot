<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.02.13
 * Time: 18:25
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\AccountContribution;
use App\Entity\ClientAccount;

class TransferFundingFormType extends AbstractType
{
    protected $em;
    protected $account;
    protected $subscriber;
    protected $isPreSaved;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->account = $options['account'];
        $this->subscriber = $options['subscriber'];
        $this->isPreSaved = $options['isPreSaved'];

        $client = $this->account->getClient();

        // Array of types without TYPE_DISTRIBUTING
        $typeChoices = [
            'Bank Transfer' =>  AccountContribution::TYPE_FUNDING_BANK,
            'Mail Check' => AccountContribution::TYPE_FUNDING_MAIL,
            'Wire Transfer' => AccountContribution::TYPE_FUNDING_WIRE,
            'I will not be funding my account at this time' => AccountContribution::TYPE_NOT_FUNDING,
        ];

        // Array of transaction_frequency values
        $frequencyTransactionChoices = $this->getChoicesForTransactionFrequency();

        $builder
            //->add('bankInformation', new BankInformationFormType($this->isPreSaved))
            ->add('bankInformation', EntityType::class, [
                'class' => 'App\\Entity\\BankInformation',
                'query_builder' => function (EntityRepository $er) use ($client) {
                    return $er->createQueryBuilder('bi')
                        ->where('bi.client_id = :client_id')
                        ->setParameter('client_id', $client->getId());
                },
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $typeChoices,
                'multiple' => false,
                'required' => false,
                'auto_initialize' => false,
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'required' => false,
            ])
            ->add('transaction_frequency', ChoiceType::class, [
                'choices' => $frequencyTransactionChoices,
                'multiple' => false,
                'required' => false,
                'auto_initialize' => false,
            ])
        ;

        if (!is_null($this->subscriber)) {
            $builder->addEventSubscriber($this->subscriber);
        }

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        /* @var $data AccountContribution */
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->has('start_transfer_date_month') && $form->has('start_transfer_date_day')) {
            $month = $form->get('start_transfer_date_month')->getData();
            $day = $form->get('start_transfer_date_day')->getData();
            $year = date('Y');

            if ($month && $day) {
                $date = new \DateTime($year.'-'.$month.'-'.$day);
                $data->setStartTransferDate($date);
            }
        }

        $this->updateData($form, $data);

        if (!$this->isPreSaved) {
            $this->validateFields($form, $data);
        }
    }

    /**
     * If need to update the attributes of the data object, depending on the form fields.
     *
     * @param FormInterface $form
     * @param mixed         $data
     */
    protected function updateData(FormInterface $form, $data)
    {
        if (AccountContribution::TYPE_FUNDING_BANK !== $data->getType()) {
            $bankInformation = $data->getBankInformation();
            if ($bankInformation && $bankInformation->getId()) {
                $this->em->remove($bankInformation);
            }

            $data->setBankInformation(null);

            if ($form->has('start_transfer_date_month') && $form->has('start_transfer_date_day')) {
                $data->setStartTransferDate(null);
            }
            if ($form->has('amount')) {
                $data->setAmount(null);
            }
            if ($form->has('transaction_frequency')) {
                $data->setTransactionFrequency(AccountContribution::TRANSACTION_FREQUENCY_ONE_TIME);
            }
            if ($form->has('account_type')) {
                $data->setAccountType(null);
            }
        }
    }

    /**
     * TODO: need refactor
     * Validate form fields.
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param mixed                                 $data
     */
    protected function validateFields(FormInterface $form, $data)
    {
        if ($form->has('type') && !in_array($data->getType(), AccountContribution::getTypeChoices())) {
            $form->get('type')->addError(new FormError('Choose an option.'));
        } else {
            if (AccountContribution::TYPE_FUNDING_BANK === $data->getType()) {
                $bankInfo = $data->getBankInformation();
                if (null === $bankInfo) {
                    $form->get('bankInformation')->addError(new FormError('Required.'));
                }

                if (!($data->getStartTransferDate() instanceof \DateTime)) {
                    $form->get('start_transfer_date_month')->addError(new FormError('Enter correct date.'));
                } else {
                    $minDate = new \DateTime('+5 days');

                    if ($data->getStartTransferDate() < $minDate) {
                        $form->get('start_transfer_date_month')->addError(
                            new FormError(
                                'The start of your transfer should be at least 5 days after today’s date.'
                            )
                        );
                    }
                }

                if (!$data->getAmount()) {
                    $form->get('amount')->addError(new FormError('Required.'));
                }

                // Array of transaction_frequency values
                $frequencyTransactionChoices = $this->getChoicesForTransactionFrequency();
                if (!in_array($data->getTransactionFrequency(), array_keys($frequencyTransactionChoices))) {
                    $form->get('transaction_frequency')->addError(new FormError('Choose an option.'));
                }

                if ($form->has('contribution_year')) {
                    $contributionYear = $data->getContributionYear();

                    if (null === $contributionYear || !is_numeric($contributionYear)) {
                        $form->get('contribution_year')->addError(new FormError('Enter valid year.'));
                    } else {
                        $currDate = new \DateTime();
                        $currYear = $currDate->format('Y');
                        $minDate = new \DateTime($currYear.'-01-01');
                        $maxDate = new \DateTime($currYear.'-04-15');

                        $startTransferDate = $data->getStartTransferDate();
                        if (($startTransferDate < $minDate) || ($startTransferDate > $maxDate)) {
                            if ($contributionYear !== $currYear) {
                                $form->get('contribution_year')->addError(
                                    new FormError(sprintf('Value should be equal %s', $currYear))
                                );
                            }
                        } else {
                            $prevYear = $currDate->add(\DateInterval::createFromDateString('-1 year'))->format('Y');

                            if ($contributionYear !== $currYear && $contributionYear !== $prevYear) {
                                $form->get('contribution_year')->addError(
                                    new FormError(sprintf('Value should be equal %s or %s', $prevYear, $currYear))
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    protected function getChoicesForTransactionFrequency()
    {
        $transactionFrequencyChoices = AccountContribution::getTransactionFrequencyChoices();

        return $transactionFrequencyChoices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AccountContribution',
            'em' => null,
            'account' => null,
            'subscriber' => null,
            'isPreSaved' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'transfer_funding_type';
    }
}
