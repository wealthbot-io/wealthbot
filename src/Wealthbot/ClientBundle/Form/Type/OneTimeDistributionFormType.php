<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.04.13
 * Time: 17:35
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\Distribution;

class OneTimeDistributionFormType extends ScheduledDistributionFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $typeChoices = [
            Distribution::TRANSFER_METHOD_BANK_TRANSFER => 'Bank Transfer',
            Distribution::TRANSFER_METHOD_RECEIVE_CHECK => 'Receive a check',
            Distribution::TRANSFER_METHOD_WIRE_TRANSFER => 'Wire Transfer',
            Distribution::TRANSFER_METHOD_NOT_FUNDING => 'I will not be funding my account at this time',
        ];

        $builder->add('transfer_method', 'choice', [
            'choices' => $typeChoices,
            'expanded' => true,
            'multiple' => false,
            'required' => false,
        ]);
    }
}
