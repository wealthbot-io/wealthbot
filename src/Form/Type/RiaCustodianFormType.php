<?php
/**
 * Created by JetBrains PhpStorm.
 * User: wealthbotdev1
 * Date: 1/14/14
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaCustodianFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('custodian', EntityType::class, [
                'label' => false,
                'class' => "App\\Entity\\Custodian",
                'property_path' => 'name',
                'id_reader' => 'id',
               // 'expanded' => true,
                'mapped' => true
            ])
            ->add('allow_non_electronically_signing', ChoiceType::class, [
               'choices' => [ 'Yes'=> true, 'No'=> false ],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_custodian';
    }
}
