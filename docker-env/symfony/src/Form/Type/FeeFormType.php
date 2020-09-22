<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Fee;
use App\Entity\User;

/**
 *
 * Class FeeFormType
 */
class FeeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder
            ->add('fee_with_retirement', NumberType::class, [
                'label' => 'Fee',
                'scale' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN,
                'grouping' => true,
            ])
            ->add('fee_without_retirement', NumberType::class, [
                'label' => 'Fee',
                'scale' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALF_EVEN,
                'grouping' => true,
            ])
            ->add('tier_bottom', NumberType::class, [
                'grouping' => true,
                'attr' => [],
                'csrf_protection' => false,
                'scale' => 2
            ])
            ->add('billingSpec');

        $refreshTierTop = function (FormInterface $form, $value) use ($factory) {
            $form
                ->add($factory->createNamed('tier_top', NumberType::class, null, [
                    'grouping' => true,
                    'auto_initialize' => false,
                    'attr' => Fee::INFINITY === $value ? ['value' => '', 'disabled' => 'readonly'] : [],
                ]))
                ->add($factory->createNamed('is_final_tier', CheckboxType::class, null, [
                    'label' => 'Is this your final tier?',
                    'attr' => Fee::INFINITY === $value ? ['checked' => 'checked'] : [],
                    'required' => false,
                   // 'mapped' => false,
                    'auto_initialize' => false,
                ]));
        };


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshTierTop) {
            $form = $event->getForm();
            $data = $event->getData();
            if (null === $data) {
                $refreshTierTop($form, null);
            } else {
                if ($data->getTierTop()) {
                    $refreshTierTop($form, $data->getTierTop());
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['is_final_tier']) && $data['is_final_tier']) {
            $data['tier_top'] = Fee::INFINITY;
            $event->setData($data);
        }
    }

    public function onSubmit(FormEvent $event)
    {
        /** @param \App\Entity\Fee $fee */
        $fee = $event->getData();

        $form = $event->getForm();
        $feeValue = $form['fee_without_retirement']->getData();
        $billingSpec = $form['billingSpec']->getData();

        $fee->setBillingSpec($billingSpec);
        $fee->setFeeWithRetirement($feeValue);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Fee',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fees';
    }
}
