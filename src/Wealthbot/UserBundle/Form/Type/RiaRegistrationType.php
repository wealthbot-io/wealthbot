<?php

namespace Wealthbot\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\Profile;

class RiaRegistrationType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', new RiaProfileType(), ['label' => ' ']);

        parent::buildForm($builder, $options);

        $builder->remove('username');

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $user->setUsername($user->getEmail());
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
        return 'ria_registration';
    }

    protected function addRiaFieldsValidator(FormBuilderInterface $builder)
    {
    }
}
