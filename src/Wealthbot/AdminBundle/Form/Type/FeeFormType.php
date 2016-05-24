<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.09.12
 * Time: 19:11
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeeFormType extends AbstractType
{
    /** @var \Wealthbot\UserBundle\Entity\User $user */
    private $user;

    public function __construct(\Wealthbot\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        $refreshTierTop = function (FormInterface $form, $value) use ($factory) {
            $form
                ->add($factory->createNamed('tier_top', 'number', null, [
                    'grouping' => true,
                    'auto_initialize' => false,
                    'attr' => $value === 1000000000000 ? ['value' => '', 'disabled' => 'readonly'] : [],
                ]))
                ->add($factory->createNamed('is_final_tier', 'checkbox', null, [
                    'label' => 'Is this your final tier?',
                    'attr' => $value === 1000000000000 ? ['checked' => 'checked'] : [],
                    'required' => false,
                    'auto_initialize' => false,
                   // 'mapped' => false,
                ]))
            ;
        };

        $builder
            ->add('fee_with_retirement', 'text', ['label' => 'Fee with retirement (%)'])
            ->add('fee_without_retirement', 'text', ['label' => 'Fee without retirement (%)'])
            ->add('tier_bottom', 'number', [
                'label' => 'Tier bottom ($)',
                'required' => true,
               // 'mapped' => false,
                'grouping' => true,
                'attr' => ['readonly' => 'readonly'],
            ])
        ;

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
            $data['tier_top'] = 1000000000000;
            $event->setData($data);
        }
    }

    public function onSubmit(FormEvent $event)
    {
        $fee = $event->getData();
        $fee->setOwner($this->user);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_fee_type';
    }
}
