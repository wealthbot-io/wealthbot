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

class RiaSubclassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expected_performance', 'number', array(
                'grouping' => true,
                'precision' => 2,
                'label' => 'Expected Performance (%)'
            ))
            ->add('accountType', 'entity', array(
                'class' => 'WealthbotRiaBundle:SubclassAccountType',
                'property' => 'name',
                'label' => 'Type'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Subclass',
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_ria_subclass_type';
    }
}
