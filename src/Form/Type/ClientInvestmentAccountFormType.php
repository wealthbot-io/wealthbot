<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.09.12
 * Time: 18:08
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientInvestmentAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('financial_institution', TextType::class)
            ->add('accountType', EntityType::class, [
                'class' => 'App\\Entity\\ClientInvestmentAccountType',
                'property' => 'name',
                'label' => 'Account Type',
            ])
            ->add('value', TextType::class, ['label' => 'Estimated Value'])
            ->add('monthly_contributions', TextType::class, ['label' => 'Estimated Monthly Contributions'])
            ->add('monthly_distributions', TextType::class, ['label' => 'Estimated Monthly Distributions'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAccount',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_client_investment_account_type';
    }
}
