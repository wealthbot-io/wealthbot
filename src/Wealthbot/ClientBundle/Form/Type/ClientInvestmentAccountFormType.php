<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.09.12
 * Time: 18:08
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientInvestmentAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('financial_institution', 'text')
            ->add('accountType', 'entity', array(
                'class' => 'WealthbotClientBundle:ClientInvestmentAccountType',
                'property' => 'name',
                'label' => 'Account Type'
            ))
            ->add('value', 'text', array('label' => 'Estimated Value'))
            ->add('monthly_contributions', 'text', array('label' => 'Estimated Monthly Contributions'))
            ->add('monthly_distributions', 'text', array('label' => 'Estimated Monthly Distributions'))
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
        return 'wealthbot_userbundle_client_investment_account_type';
    }
}
