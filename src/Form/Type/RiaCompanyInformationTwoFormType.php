<?php

namespace App\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Fee;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

/**
 *  @deprecated
 *
 * Class RiaCompanyInformationTwoFormType
 */
class RiaCompanyInformationTwoFormType extends AbstractType
{
    /** @param \App\Entity\User $user */
    private $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    private $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->isPreSave = $options['is_pre_save'];

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
            ->add('fees', CollectionType::class, [
                'entry_type' => FeeFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__name__',
                'by_reference' => false,
                'mapped' => false,
                'data' => $fees,
            ])
            ->add('minimum_billing_fee', NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
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
                if ($formBottom !== $bottom) {
                    if (round($bottom) === $maxTopTierValue) {
                        $form->get('fees')->get($key)->get('tier_top')->addError(new FormError('You already have specified final tier.'));
                        break;
                    } else {
                        $form->get('fees')->get($key)->get('tier_bottom')->addError(new FormError('Tier bottom should be %bottom%, %form_bottom% given.', [
                            '%bottom%' => number_format($bottom, 2),
                            '%form_bottom%' => number_format($formBottom, 2),
                        ]));
                    }
                }

                if ($formBottom >= $fee->getTierTop()) {
                    $form->get('fees')->get($key)->get('tier_top')->addError(new FormError('This value must be greater than tier bottom.'));
                }

                if ($fee->getFeeWithoutRetirement() >= 1) {
                    $form->get('fees')->get($key)->get('fee_without_retirement')->addError(new FormError('Fee must be greater than 0 and less then 1 (example : 0.0125).'));
                }

                $bottom = round(($fee->getTierTop() + 0.01), 2);
                if (round($fee->getTierTop()) === $maxTopTierValue) {
                    $hasTopTier = true;
                }
            }

            if (!$hasTopTier) {
                $form->addError(new FormError('You should specify final tier', [
                    '%bottom%' => $bottom,
                ]));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
            'user' => null,
            'is_pre_save' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_company_information_type';
    }
}
