<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @deprecated
 * Class FeeFormType
 * @package Wealthbot\RiaBundle\Form\Type
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
            ->add('fee_without_retirement', 'number', array(
                'label' => 'Fee',
                'precision' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALFEVEN,
                'grouping' => true
            ))
            ->add('tier_bottom', 'number', array(
                'grouping' => true,
                'attr' => array('readonly' => 'readonly'),
                'property_path' => false,
            ));

        $refreshTierTop = function (FormInterface $form, $value) use ($factory) {
            $form
                ->add($factory->createNamed('tier_top', 'number', null, array(
                    'grouping' => true,
                    'attr' => $value == Fee::INFINITY ? array('value' => '', 'disabled' => 'readonly') : array()
                )))
                ->add($factory->createNamed('is_final_tier', 'checkbox', null, array(
                    'label' => 'Is this your final tier?',
                    'attr' => $value == Fee::INFINITY ? array('checked' => 'checked') : array(),
                    'required' => false,
                    'property_path' => false,
                )));
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

        $builder->addEventListener(FormEvents::PRE_BIND, array($this, 'onPreBind'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
    }

    public function onPreBind(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['is_final_tier']) && $data['is_final_tier']) {
            $data['tier_top'] = Fee::INFINITY;
            $event->setData($data);
        }
    }

    public function onBind(FormEvent $event)
    {
        /** @var \Wealthbot\AdminBundle\Entity\Fee $fee */
        $fee = $event->getData();
        $user = $this->getUser();

        $form = $event->getForm();
        $feeValue = $form['fee_without_retirement']->getData();

        $fee->setFeeWithRetirement($feeValue);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee'
        ));
    }

    public function getName()
    {
        return 'fees';
    }
}
