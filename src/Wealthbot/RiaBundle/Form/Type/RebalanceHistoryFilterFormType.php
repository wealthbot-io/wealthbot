<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\User;

class RebalanceHistoryFilterFormType extends AbstractType
{
    private $ria;

    public function __construct(User $ria = null)
    {
        $this->ria = $ria;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', 'text', [
                'required' => false,
            ])
            ->add('date_from', 'text', [
                'required' => false,
            ])
            ->add('date_to', 'text', [
                'required' => false,
            ])
            ->add('client_id', 'hidden', [
                'required' => false,
            ])
        ;

        $factory = $builder->getFormFactory();

        if ($this->ria && $this->ria->hasGroup('all')) {
            $choices = [];

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices) {
                $form = $event->getForm();

                $form->add($factory->createNamed('set_id', 'choice', null, [
                    'choices' => $choices,
                    'required' => true,
                    'auto_initialize' => false,
                ]));
            });
        }
    }

    public function getBlockPrefix()
    {
        return 'rebalance_history_filter_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }
}
