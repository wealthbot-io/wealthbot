<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;

class SecurityTransactionFormType extends AbstractType
{
    private $riaCompanyInformation;

    public function __construct(RiaCompanyInformation $riaCompanyInformation)
    {
        $this->riaCompanyInformation = $riaCompanyInformation;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->riaCompanyInformation->getIsTransactionFees()) {
            $builder->add(
                'transaction_fee',
                NumberType::class,
                [
                    'scale' => 2,
                    'grouping' => true,
                    'required' => false,
                ]
            );
        }

        if ($this->riaCompanyInformation->getIsTransactionMinimums()) {
            $builder
                ->add(
                    'minimum_buy',
                    NumberType::class,
                    [
                        'scale' => 2,
                        'grouping' => true,
                        'required' => false,
                    ]
                )
                ->add(
                    'minimum_initial_buy',
                    NumberType::class,
                    [
                        'scale' => 2,
                        'grouping' => true,
                        'required' => false,
                    ]
                )
                ->add(
                    'minimum_sell',
                    NumberType::class,
                    [
                        'scale' => 2,
                        'grouping' => true,
                        'required' => false,
                    ]
                );
        }

        if ($this->riaCompanyInformation->getIsTransactionRedemptionFees()) {
            $builder
                ->add(
                    'redemption_penalty_interval',
                    NumberType::class,
                    [
                        'required' => false,
                    ]
                )
                ->add(
                    'redemption_fee',
                    NumberType::class,
                    [
                        'scale' => 2,
                        'grouping' => true,
                        'required' => false,
                    ]
                )
                ->add(
                    'redemption_percent',
                    PercentType::class,
                    [
                        'scale' => 2,
                        'required' => false,
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\SecurityTransaction',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_admin_security_transaction_type';
    }
}
