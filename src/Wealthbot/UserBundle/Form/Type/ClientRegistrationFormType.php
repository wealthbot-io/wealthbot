<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\Profile;

class ClientRegistrationFormType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username')
            ->add('profile', new ClientProfileType())
            ->add('is_accepted', 'checkbox', [
                'required' => true,
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'class' => 'pull-left'
                ]
            ])
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $client = $event->getData();

            if (!$form->get('is_accepted')->getData()) {
                $form->get('is_accepted')->addError(new FormError('Required.'));
            }

            if (!$form->get('profile')->get('first_name')->getData()) {
                $form->get('profile')->get('first_name')->addError(new FormError('Required.'));
            }

            if (!$form->get('profile')->get('last_name')->getData()) {
                $form->get('profile')->get('last_name')->addError(new FormError('Required.'));
            }

            $client->setUsername($client->getEmail());
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'cascade_validation' => true,
            'validation_groups' => ['Registration', 'password'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_registration';
    }
}
