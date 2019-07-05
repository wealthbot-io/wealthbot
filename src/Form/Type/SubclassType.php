<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 12:03
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubclassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Subclass'])
            ->add('expected_performance', TextType::class, ['label' => 'Expected Performance (%)'])
            ->add('assetClass', EntityType::class, [
                'class' => 'App\\Entity\\AssetClass',
                'placeholder' => 'Choose Asset Class',
            ])
            ->add('accountType')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Subclass',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_subclass_type';
    }
}
