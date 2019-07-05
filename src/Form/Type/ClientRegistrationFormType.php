<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'options' => array(
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => array(
                        'autocomplete' => 'new-password',
                    ),
                ),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('profile', ClientProfileType::class)
            ->add('is_accepted', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'class' => 'pull-left',
                ],
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
            'data_class' => 'App\Entity\User',
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
