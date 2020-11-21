<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Job;

class RebalanceFormType extends AbstractType
{
    private $clientValueIds = [];

    private $isShowType;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->clientValueIds = array_combine($options['client_value_ids'], $options['client_value_ids']);
        $this->isShowType = $options['is_show_type'];

        $builder->add('is_all', CheckboxType::class, [
            'label' => 'Check all',
            'required' => false,
        ]);

        if ($this->isShowType) {
            $builder->add('rebalance_type', ChoiceType::class, [
                'choices' => array_combine(array_values(Job::rebalanceTypeChoicesForSelect()), array_keys(Job::rebalanceTypeChoicesForSelect())),
                'required' => true,
            ]);
        }

        $factory = $builder->getFormFactory();
        $choices = $this->clientValueIds;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices) {
            $form = $event->getForm();

            $form->add($factory->createNamed('client_value', ChoiceType::class, null, [
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
                'auto_initialize' => false,
            ]));
        });
    }

    public function getBlockPrefix()
    {
        return 'rebalance_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'client_value_ids' => null,
            'is_show_type' => null
        ]);
    }
}
