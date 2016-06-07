<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\Job;

class RebalanceFormType extends AbstractType
{
    private $clientValueIds = [];

    private $isShowType;

    public function __construct(array $clientValueIds, $isShowType = false)
    {
        $this->clientValueIds = array_combine($clientValueIds, $clientValueIds);
        $this->isShowType = $isShowType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_all', 'checkbox', [
            'label' => 'Check all',
            'required' => false,
        ]);

        if ($this->isShowType) {
            $builder->add('rebalance_type', 'choice', [
                'choices' => Job::rebalanceTypeChoicesForSelect(),
                'required' => true,
            ]);
        }

        $factory = $builder->getFormFactory();
        $choices = $this->clientValueIds;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices) {
            $form = $event->getForm();

            $form->add($factory->createNamed('client_value', 'choice', null, [
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
        ]);
    }
}
