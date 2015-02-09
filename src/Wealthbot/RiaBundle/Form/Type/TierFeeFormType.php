<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TierFeeFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fee_without_retirement', 'number', array(
                'label' => 'Fee',
                'precision' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALFEVEN,
            ))
            ->add('tier_top', 'number')
        ;

        $builder->addEventListener(FormEvents::PRE_BIND, array($this, 'onPreBind'));
    }

    public function onPreBind(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['is_final_tier']) && $data['is_final_tier']) {
            $data['tier_top'] = 1000000000000;
            $event->setData($data);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee',
            'validation_groups' => array('tier')
        ));
    }

    public function getName()
    {
        return 'fees';
    }
}
