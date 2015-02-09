<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 20.03.14
 * Time: 22:00
 */
namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\ClientBundle\Entity\Distribution;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class ScheduledDistributionFormType extends AbstractType
{
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $distribution = $builder->getData();
        if (null == $distribution->getFrequency()) {
            $distribution->setFrequency(Distribution::FREQUENCY_EVERY_OTHER_WEEK);
        }
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('frequency', 'choice', array(
                'expanded' => true,
                'label' => 'Frequency of transaction: ',
                'choices' => Distribution::getFrequencyChoices()
            ))
            ->add('amount', 'money', array(
                'attr' => array('class' => 'input-mini'),
                'currency' => 'USD',
                'label' => 'Amount: '
            )
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBindData'));
    }

    public function onPresetData(FormEvent $event)
    {
        $distribution = $event->getData();
        $form = $event->getForm();
        $date = $distribution->getTransferDate();

        $form
            ->add($this->factory->createNamed('month', 'number', null, array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => 'MM',
                ),
                'label' => 'Start of transfer: ',
                'property_path' => false,
                'data' => $date ? $date->format("m") : null,
                'constraints' => array(
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Range(array(
                        'min' => 1,
                        'max' => 12,
                        'minMessage' => 'Month should be equal or greater than 1.',
                        'maxMessage' => 'Month should be equal or less than 12.'
                    ))
            ))))
            ->add($this->factory->createNamed('day', 'number', null, array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => 'DD',
                ),
                'property_path' => false,
                'data' => $date ? $date->format("d") : null,
                'constraints' => array(
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Range(array(
                        'min' => 1,
                        'max' => 31,
                        'minMessage' => 'Day should be equal or greater than 1.',
                        'maxMessage' => 'Day should be equal or less than 31.'
                    ))
            ))))
            ;
    }

    public function onBindData(FormEvent $event)
    {
        $distribution = $event->getData();
        $form = $event->getForm();

        if ($form->has('month') && $form->has('day')) {
            $date = new \DateTime();
            $year = $date->format("Y");
            $month = $form->get('month')->getData();
            $day = $form->get('day')->getData();
            $date->setDate($year, $month, $day);
            $distribution->setTransferDate($date);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\Distribution'
        ));
    }

    public function getName()
    {
        return 'scheduled_distribution_form';
    }
}
