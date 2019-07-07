<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaSubclassCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, [
            'required' => false,
            'allow_add' => true,
            'prototype' => true,
            'entry_type' => new \App\Form\Type\RiaSubclassType(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Collection\RiaSubclassCollection',
            'cascade_validation' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_ria_subclass_collection_type';
    }
}
