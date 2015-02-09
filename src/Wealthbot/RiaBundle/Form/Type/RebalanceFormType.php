<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\Job;
use Wealthbot\AdminBundle\Entity\RebalancerAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RebalanceFormType extends AbstractType {

    private $clientValueIds = array();

    private $isShowType;

    public function __construct(array $clientValueIds, $isShowType = false)
    {
        $this->clientValueIds = array_combine($clientValueIds, $clientValueIds);
        $this->isShowType = $isShowType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_all', 'checkbox', array(
            'label' => 'Check all',
            'required' => false
        ));

        if ($this->isShowType) {
            $builder->add('rebalance_type', 'choice', array(
                'choices'   => Job::rebalanceTypeChoicesForSelect(),
                'required'  => true,
            ));
        }

        $factory = $builder->getFormFactory();
        $choices = $this->clientValueIds;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($factory, $choices) {
            $form = $event->getForm();

            $form->add($factory->createNamed('client_value', 'choice', null, array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true
            )));
        });
    }

    public function getName()
    {
        return 'rebalance_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => true,
        ));
    }
}