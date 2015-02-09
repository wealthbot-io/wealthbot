<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Form\Type\RiaProfileType;

class RiaRegistrationType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', new RiaProfileType(), array('label' => ' '));

        parent::buildForm($builder, $options);

        $builder->remove('username');

        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event)  {
            $user = $event->getData();
            $user->setUsername($user->getEmail());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'cascade_validation' => true,
            'validation_groups'  => array('Registration', 'password')
        ));
    }

    public function getName()
    {
        return 'ria_registration';
    }

    protected function addRiaFieldsValidator(FormBuilderInterface $builder)
    {

    }
}
