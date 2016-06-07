<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RiaAlertsConfigurationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_client_portfolio_suggestion', 'checkbox', [
                'label' => 'Client Portfolio Suggestion',
                'required' => false,
            ])
            ->add('is_client_driven_account_closures', 'checkbox', [
                'label' => 'Client Driven Account Closures',
                'required' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_ria_alerts_configuration_form_type';
    }
}
