<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.10.12
 * Time: 18:27
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminFeesType extends AbstractType
{
    /** @param \App\Entity\User $owner */
    private $owner;

    /** @param \App\Entity\User $appointedUser */
    private $appointedUser;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->owner = $options['owner'];
        $this->appointedUser = $options['appointedUser'];

        $builder->add('fees', CollectionType::class, [
            'entry_type' => \App\Form\Type\FeeFormType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'prototype_name' => 'fee__name__',
            'by_reference' => false,
           // 'mapped' => false,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\User',
            'owner' => null,
            'appointedUser' => null,
            'csrf_protection' => false
        ));
    }



    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $fees = $form->get('fees')->getData();
        $maxTopTierValue = 1000000000000;

        $bottom = 0;
        $hasTopTier = false;

        foreach ($fees as $key => $fee) {
            $formBottom = round($form->get('fees')->get($key)->get('tier_bottom')->getData(), 2);

            if ($formBottom !== $bottom) {
                if (round($bottom) === $maxTopTierValue) {
                    $form->get('fees')->get($key)->get('tier_top')->addError(
                        new FormError('You already have specified final tier.')
                    );
                    break;
                };
            }

            if ($formBottom >= $fee->getTierTop()) {
                $form->get('fees')->get($key)->get('tier_top')->addError(
                    new FormError('This value must be greater than tier bottom.')
                );
            }

            if ($fee->getFeeWithRetirement() >= 1) {
                $form->get('fees')->get($key)->get('fee_with_retirement')->addError(
                    new FormError('Fee must be greater than 0 and less then 1 (example : 0.0125).')
                );
            }

            if ($fee->getFeeWithoutRetirement() >= 1) {
                $form->get('fees')->get($key)->get('fee_without_retirement')->addError(
                    new FormError('Fee must be greater than 0 and less then 1 (example : 0.0125).')
                );
            }

            $bottom = round(($fee->getTierTop() + 0.01), 2);
            if (round($fee->getTierTop()) === $maxTopTierValue) {
                $hasTopTier = true;
            }
        }
    }

    public function getBlockPrefix()
    {
        return 'rx_admin_fee_type';
    }
}
