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
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientInvestmentAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('financial_institution', 'text')
            ->add('accountType', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\ClientInvestmentAccountType',
                'property' => 'name',
                'label' => 'Account Type',
            ])
            ->add('value', 'text', ['label' => 'Estimated Value'])
            ->add('monthly_contributions', 'text', ['label' => 'Estimated Monthly Contributions'])
            ->add('monthly_distributions', 'text', ['label' => 'Estimated Monthly Distributions'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_client_investment_account_type';
    }
}
