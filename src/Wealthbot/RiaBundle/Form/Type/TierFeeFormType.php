<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TierFeeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fee_without_retirement', 'number', [
                'label' => 'Fee',
                'precision' => 4,
                'rounding_mode' => IntegerToLocalizedStringTransformer::ROUND_HALFEVEN,
            ])
            ->add('tier_top', 'number')
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['is_final_tier']) && $data['is_final_tier']) {
            $data['tier_top'] = 1000000000000;
            $event->setData($data);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\Fee',
            'validation_groups' => ['tier'],
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'fees';
    }
}
