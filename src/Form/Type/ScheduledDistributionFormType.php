<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 20.03.14
 * Time: 22:00.
 */

namespace App\Form\Type;

use App\Entity\AccountContribution;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use App\Entity\Distribution;

class ScheduledDistributionFormType extends AbstractType
{
    protected $factory;
    protected $subscriber;


    protected function getChoicesForTransactionFrequency()
    {
        $transactionFrequencyChoices = AccountContribution::getTransactionFrequencyChoices();

        return $transactionFrequencyChoices;
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Array of transaction_frequency values
        $frequencyTransactionChoices = $this->getChoicesForTransactionFrequency();



        $client = $options['client'];
        $this->subscriber = $options['subscriber'];

        $distribution = $builder->getData();
        if (null === $distribution->getFrequency()) {
            $distribution->setFrequency(Distribution::FREQUENCY_EVERY_OTHER_WEEK);
        };
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('frequency', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'None of all',
                'choices' => Distribution::getFrequencyChoices(),
                'labels'=>false,
            ])
            ->add(
                'amount',
                MoneyType::class,
                [
                'attr' => ['class' => 'input-mini'],
                'currency' => 'USD',
                'label' => 'Amount: ',
            ]
            );

        $builder->add('bankInformation', EntityType::class, [
                 'class' => 'App\\Entity\\BankInformation',
                 'query_builder' => function (EntityRepository $er) use ($client) {
                     return $er->createQueryBuilder('bi')
                         ->where('bi.client_id = :client_id')
                         ->setParameter('client_id', $client->getId());
                 },
                 'expanded' => true,
                 'multiple' => false,
                 'required' => false,
             ]);

        $builder->add('frequency', ChoiceType::class, [
                    'choices' => $frequencyTransactionChoices,
                    'multiple' => false,
                    'required' => false,
                    'auto_initialize' => false,
                ]);


        if (!is_null($this->subscriber)) {
            $builder->addEventSubscriber($this->subscriber);
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPresetData(FormEvent $event)
    {
        $distribution = $event->getData();
        $form = $event->getForm();
        $date = $distribution->getTransferDate();

        $form
            ->add($this->factory->createNamed('month', NumberType::class, null, [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => 'MM',
                ],
                'label' => 'Start of transfer: ',
                'mapped' => false,
                'auto_initialize' => false,
                'data' => $date ? $date->format('m') : null,
                'constraints' => [
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Range([
                        'min' => 1,
                        'max' => 12,
                        'minMessage' => 'Month should be equal or greater than 1.',
                        'maxMessage' => 'Month should be equal or less than 12.',
                    ]),
            ], ]))
            ->add($this->factory->createNamed('day', NumberType::class, null, [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => 'DD',
                ],
                'mapped' => false,
                'auto_initialize' => false,
                'data' => $date ? $date->format('d') : null,
                'constraints' => [
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Range([
                        'min' => 1,
                        'max' => 31,
                        'minMessage' => 'Day should be equal or greater than 1.',
                        'maxMessage' => 'Day should be equal or less than 31.',
                    ]),
            ], ]))
            ;
    }

    public function onSubmitData(FormEvent $event)
    {
        $distribution = $event->getData();
        $form = $event->getForm();

        if ($form->has('month') && $form->has('day')) {
            $date = new \DateTime();
            $year = $date->format('Y');
            $month = $form->get('month')->getData();
            $day = $form->get('day')->getData();
            $date->setDate($year, $month, $day);
            $distribution->setTransferDate($date);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Distribution',
            'client' => null,
            'subscriber' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'scheduled_distribution_form';
    }
}
