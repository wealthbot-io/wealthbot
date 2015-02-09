<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\RebalancerAction;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RebalanceHistoryFilterFormType extends AbstractType {

    private $ria;

    public function __construct(User $ria = null)
    {
        $this->ria = $ria;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', 'text', array(
                'required' => false,
            ))
            ->add('date_from', 'text', array(
                'required' => false
            ))
            ->add('date_to', 'text', array(
                'required' => false
            ))
            ->add('client_id', 'hidden', array(
                'required' => false
            ))
        ;

        $factory = $builder->getFormFactory();

        if ($this->ria && $this->ria->hasGroup('all')) {

            $choices = array();

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($factory, $choices) {
                $form = $event->getForm();

                $form->add($factory->createNamed('set_id', 'choice', null, array(
                    'choices' => $choices,
                    'required' => true
                )));
            });
        }
    }

    public function getName()
    {
        return 'rebalance_history_filter_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => true,
        ));
    }
}