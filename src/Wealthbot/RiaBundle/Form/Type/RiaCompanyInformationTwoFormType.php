<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Entity\Fee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 *  @deprecated
 *
 * Class RiaCompanyInformationTwoFormType
 * @package Wealthbot\RiaBundle\Form\Type
 */
class RiaCompanyInformationTwoFormType extends AbstractType
{
    /** @var \Wealthbot\UserBundle\Entity\User $user */
    private $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    private $factory;

    public function __construct(User $user, $isPreSave = false)
    {
        $this->user = $user;
        $this->isPreSave = $isPreSave;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        if ($billingSpec = $this->user->getAppointedBillingSpec()) {
            $fees = $billingSpec->getFees();
        } else {
            $fees = new ArrayCollection();
        }
        if (!$fees->count()) {
            $fee = new Fee();
            $fees[] = $fee;
        }

        $builder
            ->add('fees', 'collection', array(
                'type' => new FeeFormType($this->user),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__name__',
                'by_reference' => false,
                'property_path' => false,
                'data' => $fees
            ))
            ->add('minimum_billing_fee', 'number', array(
                'precision' => 2,
                'grouping' => true,
                'required' => false
            ))
        ;

        $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
    }

    public function onBind(FormEvent $event)
    {
        /** @var RiaCompanyInformation $data */
        $data = $event->getData();
        $form = $event->getForm();
        $maxTopTierValue = 1000000000000;

        $fees = $form->get('fees')->getData();

        if (!$this->isPreSave) {
            $bottom = 0;
            $hasTopTier = false;

            foreach ($fees as $key => $fee) {
                $formBottom = round($form->get('fees')->get($key)->get('tier_bottom')->getData(), 2);
                if($formBottom != $bottom){
                    if (round($bottom) == $maxTopTierValue) {
                        $form->get('fees')->get($key)->get('tier_top')->addError(new FormError("You already have specified final tier."));
                        break;
                    } else {
                        $form->get('fees')->get($key)->get('tier_bottom')->addError(new FormError("Tier bottom should be %bottom%, %form_bottom% given.", array(
                            '%bottom%' => number_format($bottom, 2),
                            '%form_bottom%' => number_format($formBottom, 2)
                        )));
                    }
                }

                if($formBottom >= $fee->getTierTop()){
                    $form->get('fees')->get($key)->get('tier_top')->addError(new FormError("This value must be greater than tier bottom."));
                }

                if($fee->getFeeWithoutRetirement() >= 1) {
                    $form->get('fees')->get($key)->get('fee_without_retirement')->addError(new FormError("Fee must be greater than 0 and less then 1 (example : 0.0125)."));
                }

                $bottom = round( ($fee->getTierTop() + 0.01), 2);
                if (round($fee->getTierTop()) == $maxTopTierValue) {
                    $hasTopTier = true;
                }
            }

            if (!$hasTopTier) {
                $form->addError(new FormError('You should specify final tier', array(
                    '%bottom%' => $bottom
                )));
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation',
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
