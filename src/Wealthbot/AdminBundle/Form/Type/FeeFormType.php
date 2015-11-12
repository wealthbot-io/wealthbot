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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

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
                ->add($factory->createNamed('tier_top', 'number', null, array(
                    'grouping' => true,
                    'attr' => $value == 1000000000000 ? array('value' => '', 'disabled' => 'readonly') : array()
                )))
                ->add($factory->createNamed('is_final_tier', 'checkbox', null, array(
                    'label' => 'Is this your final tier?',
                    'attr' => $value == 1000000000000 ? array('checked' => 'checked') : array(),
                    'required' => false,
                    'property_path' => false,
                )))
            ;
        };

        $builder
            ->add('fee_with_retirement', 'text', array('label' => 'Fee with retirement (%)'))
            ->add('fee_without_retirement', 'text', array('label' => 'Fee without retirement (%)'))
            ->add('tier_bottom', 'number', array(
                'label' => 'Tier bottom ($)',
                'required' => true,
                'property_path' => false,
                'grouping' => true,
                'attr' => array('readonly' => 'readonly')
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshTierTop) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === null) {
                $refreshTierTop($form, null);
            } else {
                if($data->getTierTop()) {
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
            $data['tier_top'] = 1000000000000;
            $event->setData($data);
        }
    }

    public function onBind(FormEvent $event)
    {
        $fee = $event->getData();
        $fee->setOwner($this->user);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee'
        ));
    }

    public function getName()
    {
        return 'wealthbot_adminbundle_fee_type';
    }
}
