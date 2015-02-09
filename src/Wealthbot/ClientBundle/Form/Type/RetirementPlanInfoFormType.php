<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.02.13
 * Time: 13:08
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RetirementPlanInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('financial_institution')
            ->add('account_number')
            ->add('account_description')
            ->add('web_address_login', 'url')
            ->add('username')
            ->add('password', 'password');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\RetirementPlanInformation'
        ));
    }

    public function getName()
    {
        return 'retirement_plan_info';
    }
}