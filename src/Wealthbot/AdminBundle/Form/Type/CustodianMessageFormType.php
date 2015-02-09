<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 14:06
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustodianMessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', 'hidden', array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\CustodianMessage'
        ));
    }

    public function getName()
    {
        return 'custodian_message';
    }

}