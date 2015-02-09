<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.12
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\AssetClass;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Entity\SecurityTransaction;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'number',
                array(
                    'precision' => 2,
                    'grouping' => true,
                    'required' => false
                )
            );
        }

        if ($this->riaCompanyInformation->getIsTransactionMinimums()) {
            $builder
                ->add(
                    'minimum_buy',
                    'number',
                    array(
                        'precision' => 2,
                        'grouping' => true,
                        'required' => false
                    )
                )
                ->add(
                    'minimum_initial_buy',
                    'number',
                    array(
                        'precision' => 2,
                        'grouping' => true,
                        'required' => false
                    )
                )
                ->add(
                    'minimum_sell',
                    'number',
                    array(
                        'precision' => 2,
                        'grouping' => true,
                        'required' => false
                    )
                );
        }

        if ($this->riaCompanyInformation->getIsTransactionRedemptionFees()) {
            $builder
                ->add(
                    'redemption_penalty_interval',
                    'number',
                    array(
                        'required' => false
                    )
                )
                ->add(
                    'redemption_fee',
                    'number',
                    array(
                        'precision' => 2,
                        'grouping' => true,
                        'required' => false
                    )
                )
                ->add(
                    'redemption_percent',
                    'percent',
                    array(
                        'precision' => 2,
                        'required' => false
                    )
                );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\SecurityTransaction'
        ));
    }

    public function getName()
    {
        return 'wealthbot_admin_security_transaction_type';
    }
}
