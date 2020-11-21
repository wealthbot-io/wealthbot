<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.03.13
 * Time: 18:02
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class ClientAddressFormType extends ClientProfileFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('first_name')
            ->remove('middle_name')
            ->remove('last_name')
            ->remove('birth_date')
            ->remove('phone_number')
            ->remove('citizenship')
            ->remove('marital_status')
            ->remove('spouse')
            ->remove('annual_income')
            ->remove('estimated_income_tax')
            ->remove('liquid_net_worth')
            ->remove('employment_type')
        ;
    }

    public function getBlockPrefix()
    {
        return 'client_address';
    }
}
