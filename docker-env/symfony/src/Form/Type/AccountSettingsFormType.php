<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Model\ClientAccount;
use App\Model\SystemAccount;
use App\Entity\User;

class AccountSettingsFormType extends AbstractType
{
    /**
     * @var FormFactory
     */
    protected $factory;
    protected $em;

    protected function getBillingAccountChoices($account)
    {
        $billingAccountChoices = [];
        foreach ($account->getClient()->getSystemAccounts() as $billingAccount) {
            /* @param \App\Entity\SystemAccount $billingAccount */
            $billingAccountId = $billingAccount->getId();
            $billingAccountOwner = $billingAccount->getClientAccount()->getPrimaryApplicant();
            $billingAccountName = $billingAccountOwner->getFirstName().' '.$billingAccountOwner->getLastName();

            $billingAccountChoices[$billingAccountId] = $billingAccount->getAccountNumber().' '.$billingAccountName;
        }

        return $billingAccountChoices;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $account = $builder->getData();

        $this->factory = $builder->getFormFactory();
        $statusChoices = [
            SystemAccount::STATUS_ACTIVE => 'Account active',
            SystemAccount::STATUS_CLOSED => 'Account closed',
        ];

        $builder
            ->add('dateClosed', DateType::class, [
                'attr' => ['class' => 'jq-ce-date input-small'],
                'format' => 'MM-dd-yyyy',
                'property_path' => 'systemAccount.closed',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'attr' => ['class' => 'input-medium'],
                'property_path' => 'systemAccount.status',
                'choices' => $statusChoices,
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'label' => 'First Name',
                'property_path' => 'primaryApplicant.firstName',
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'label' => 'Last Name',
                'property_path' => 'primaryApplicant.lastName',
            ])
        ;

        $builder
            ->add('accountNumber', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'label' => 'Account Number: ',
                'property_path' => 'systemAccount.accountNumber',
            ])
            ->add('accountType', ChoiceType::class, [
                'attr' => ['class' => 'input-xlarge'],
                'choices' => SystemAccount::getTypeChoices(),
                'label' => 'Account Type: ',
                'property_path' => 'systemAccount.type',
            ])
            ->add('sasCash', MoneyType::class, [
                'attr' => ['class' => 'input-mini'],
                'currency' => 'USD',
                'label' => 'SAS Cash',
            ])
            ->add('performanceInception', DateType::class, [
                'attr' => ['class' => 'jq-date input-small'],
                'format' => 'MM-dd-yyyy',
                'label' => 'Performance Inception: ',
                'property_path' => 'systemAccount.performanceInception',
                'widget' => 'single_text',
            ])
            ->add('billingInception', DateType::class, [
                'attr' => ['class' => 'jq-date input-small'],
                'format' => 'MM-dd-yyyy',
                'label' => 'Billing Inception: ',
                'property_path' => 'systemAccount.billingInception',
                'widget' => 'single_text',
            ])
            ->add('billingAccount', EntityType::class, [
                'class' => 'App\\Entity\\SystemAccount',
                'label' => 'Billing Account: ',
                'property_path' => 'systemAccount.billingAccount',
                'query_builder' => function (EntityRepository $er) use ($account) {
                    return $er->createQueryBuilder('systemAccounts')
                            ->where('systemAccounts.client = :client')
                            ->setParameter('client', $account->getClient());
                },
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $account = $event->getData();
        $systemAccount = $account->getSystemAccount();
        $rebalancerActionsRepo = $this->em->getRepository('App\Entity\RebalancerAction');

        if (null === $systemAccount->getBillingAccount()) {
            $systemAccount->setBillingAccount($systemAccount);
        }
        if (SystemAccount::STATUS_CLOSED === $systemAccount->getStatus() && null === $systemAccount->getClosed()) {
            $systemAccount->setClosed(new \DateTime());
        }

        $firstRebalance = $rebalancerActionsRepo
            ->findOneBy([
                'systemClientAccount' => $account->getSystemAccount(),
            ], [
                'started_at' => 'DESC',
            ])
        ;

        if (null !== $firstRebalance) {
            $initialRebalanceDate = $firstRebalance->getStartedAt();
            if (null === $systemAccount->getPerformanceInception()) {
                $systemAccount->setPerformanceInception($initialRebalanceDate);
            }
            if (null === $systemAccount->getBillingInception()) {
                $systemAccount->setBillingInception($initialRebalanceDate);
            }
        }

        $ssn = $account->getPrimaryApplicant()->getSsnTin();
        preg_match("~(\d{3})(\d{2})(\d{4})~", $ssn, $matches);
        if (empty($matches)) {
            $matches = [1 => '', '', ''];
        }

        $form
            ->add($this->factory->createNamed('ssn1', NumberType::class, null, [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ],
                'mapped' => false,
                'data' => $matches[1],
                'auto_initialize' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 3,
                        'max' => 3,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))
            ->add($this->factory->createNamed('ssn2', NumberType::class, null, [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ],
                'mapped' => false,
                'data' => $matches[2],
                'auto_initialize' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 2,
                        'max' => 2,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))
            ->add($this->factory->createNamed('ssn3', NumberType::class, null, [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '####',
                ],
                'mapped' => false,
                'data' => $matches[3],
                'auto_initialize' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 4,
                        'max' => 4,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))
        ;
    }

    public function onSubmitData(FormEvent $event)
    {
        /* @var ClientAccount $account */
        $account = $event->getData();

        /* @var User $user */
        $user = $account->getClient();
        $form = $event->getForm();

        $hasUnclosed = false;

        /* @var SystemAccount $systemAccount */
        foreach ($user->getSystemAccounts() as $systemAccount) {
            if (SystemAccount::STATUS_CLOSED !== $systemAccount->getStatus()) {
                $hasUnclosed = true;
            }
        }

        if (!$hasUnclosed && $user->isEnabled()) {
            $user
                ->setEnabled($hasUnclosed)
                ->setClosed(new \DateTime())
            ;
        }

        if (SystemAccount::STATUS_CLOSED === $form->get('status')->getData() && null === $form->get('dateClosed')->getData()) {
            $form->get('dateClosed')->addError(new FormError('This field is required when closing account'));
        }

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $account->getPrimaryApplicant()->setSsnTin($ssn1.$ssn2.$ssn3);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAccount',
            'em' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'account_settings';
    }
}
