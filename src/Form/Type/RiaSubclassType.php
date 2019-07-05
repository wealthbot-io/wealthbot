<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaSubclassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expected_performance', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Expected Performance (%)',
            ])
            ->add('accountType', EntityType::class, [
                'class' => 'App\\Entity\\SubclassAccountType',
                'property' => 'name',
                'label' => 'Type',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Subclass',
            'cascade_validation' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_ria_subclass_type';
    }
}
