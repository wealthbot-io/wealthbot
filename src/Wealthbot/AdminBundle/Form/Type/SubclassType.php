<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 12:03
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubclassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Subclass'))
            ->add('expected_performance', 'text', array('label' => 'Expected Performance (%)'))
            ->add('assetClass', 'entity', array(
                'class' => 'WealthbotAdminBundle:AssetClass',
                'empty_value' => 'Choose Asset Class'
            ))
            ->add('accountType')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Subclass'
        ));
    }

    public function getName()
    {
        return 'wealthbot_adminbundle_subclass_type';
    }
}
