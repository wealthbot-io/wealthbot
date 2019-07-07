<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlatFeeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fee_without_retirement', NumberType::class, [
                'label' => 'Fee',
                'scale' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN,
                'grouping' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Fee',
            'validation_groups' => ['flat'],
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fees';
    }
}
