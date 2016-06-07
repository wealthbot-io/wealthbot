<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 20.03.14
 * Time: 22:00.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Wealthbot\ClientBundle\Entity\Distribution;

class ScheduledDistributionFormType extends AbstractType
{
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $distribution = $builder->getData();
        if (null === $distribution->getFrequency()) {
            $distribution->setFrequency(Distribution::FREQUENCY_EVERY_OTHER_WEEK);
        }
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('frequency', 'choice', [
                'expanded' => true,
                'label' => 'Frequency of transaction: ',
                'choices' => Distribution::getFrequencyChoices(),
            ])
            ->add('amount', 'money', [
                'attr' => ['class' => 'input-mini'],
                'currency' => 'USD',
                'label' => 'Amount: ',
            ]
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPresetData(FormEvent $event)
    {
        $distribution = $event->getData();
        $form = $event->getForm();
        $date = $distribution->getTransferDate();

        $form
            ->add($this->factory->createNamed('month', 'number', null, [
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
            ->add($this->factory->createNamed('day', 'number', null, [
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
            'data_class' => 'Wealthbot\ClientBundle\Entity\Distribution',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'scheduled_distribution_form';
    }
}
