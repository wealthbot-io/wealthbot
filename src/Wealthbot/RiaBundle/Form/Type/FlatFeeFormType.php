<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FlatFeeFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fee_without_retirement', 'number', array(
                'label' => 'Fee',
                'precision' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALFEVEN,
                'grouping' => true,
            ))
        ;

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee',
            'validation_groups' => array('flat'),

        ));
    }

    public function getName()
    {
        return 'fees';
    }
}
