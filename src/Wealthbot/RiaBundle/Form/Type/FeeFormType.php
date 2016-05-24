<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\UserBundle\Entity\User;

/**
 * @deprecated
 * Class FeeFormType
 */
class FeeFormType extends AbstractType
{
    /** @var \Wealthbot\UserBundle\Entity\User */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $builder
            ->add('fee_without_retirement', 'number', [
                'label' => 'Fee',
                'precision' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALFEVEN,
                'grouping' => true,
            ])
            ->add('tier_bottom', 'number', [
                'grouping' => true,
                'attr' => ['readonly' => 'readonly'],
               // 'mapped' => false,
            ]);

        $refreshTierTop = function (FormInterface $form, $value) use ($factory) {
            $form
                ->add($factory->createNamed('tier_top', 'number', null, [
                    'grouping' => true,
                    'auto_initialize' => false,
                    'attr' => $value === Fee::INFINITY ? ['value' => '', 'disabled' => 'readonly'] : [],
                ]))
                ->add($factory->createNamed('is_final_tier', 'checkbox', null, [
                    'label' => 'Is this your final tier?',
                    'attr' => $value === Fee::INFINITY ? ['checked' => 'checked'] : [],
                    'required' => false,
                   // 'mapped' => false,
                    'auto_initialize' => false,
                ]));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshTierTop) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === null) {
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
        /** @var \Wealthbot\AdminBundle\Entity\Fee $fee */
        $fee = $event->getData();
        $user = $this->getUser();

        $form = $event->getForm();
        $feeValue = $form['fee_without_retirement']->getData();

        $fee->setFeeWithRetirement($feeValue);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fees';
    }
}
