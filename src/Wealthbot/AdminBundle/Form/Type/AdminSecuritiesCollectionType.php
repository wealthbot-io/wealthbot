<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:10
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminSecuritiesCollectionType extends AbstractType
{
    /** @var \Symfony\Component\Form\FormFactoryInterface $factory */
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', 'collection', [
            'required' => false,
            'allow_add' => true,
            'prototype' => true,
            'type' => new \Wealthbot\AdminBundle\Form\Type\SecurityFormType($this->factory),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Collection\AdminSecuritiesCollection',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_admin_securities_collection_type';
    }
}
