<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.09.12
 * Time: 14:38
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientRetirementAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountType', 'entity', array(
                'class' => 'WealthbotClientBundle:ClientAccountType',
                'property' => 'name',
                'label' => 'Type'
            ))
            ->add('value', 'text', array('label' => 'Estimated Value'))
            ->add('monthly_contributions', 'text', array('label' => 'Estimated Monthly Contributions'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount'
        ));
    }

    public function getName()
    {
        return 'wealthbot_userbundle_client_retirement_account_type';
    }
}
