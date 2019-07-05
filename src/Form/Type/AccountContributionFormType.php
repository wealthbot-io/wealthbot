<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 18:30
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class AccountContributionFormType extends TransferFundingFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('type');
        $builder->remove('transaction_frequency');
    }
}
