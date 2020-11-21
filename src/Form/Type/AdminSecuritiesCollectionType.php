<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:10
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminSecuritiesCollectionType extends AbstractType
{
    /** @var \Symfony\Component\Form\FormFactoryInterface $factory */
    private $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $options['factory'];


        $builder->add('items', CollectionType::class, [
            'required' => false,
            'allow_add' => true,
            'prototype' => true,
            'type' => \App\Form\Type\SecurityFormType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Collection\AdminSecuritiesCollection',
            'factory' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_admin_securities_collection_type';
    }
}
