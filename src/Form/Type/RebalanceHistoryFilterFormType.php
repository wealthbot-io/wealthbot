<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class RebalanceHistoryFilterFormType extends AbstractType
{
    private $ria;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];

        $builder
            ->add('client', TextType::class, [
                'required' => false,
            ])
            ->add('date_from', TextType::class, [
                'required' => false,
            ])
            ->add('date_to', TextType::class, [
                'required' => false,
            ])
            ->add('client_id', HiddenType::class, [
                'required' => false,
            ])
        ;

        $factory = $builder->getFormFactory();

        if ($this->ria && $this->ria->hasGroup('all')) {
            $choices = [];

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices) {
                $form = $event->getForm();

                $form->add($factory->createNamed('set_id', ChoiceType::class, null, [
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
            'ria' => null
        ]);
    }
}
