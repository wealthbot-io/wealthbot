<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaSubclassCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', 'collection', array(
            'required' => false,
            'allow_add' => true,
            'prototype' => true,
            'type' => new \Wealthbot\RiaBundle\Form\Type\RiaSubclassType(),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Collection\RiaSubclassCollection',
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_ria_subclass_collection_type';
    }
}
