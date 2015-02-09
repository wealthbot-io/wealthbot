<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Wealthbot\UserBundle\Entity\Profile;

class ClientRegistrationFormType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username')
            ->add('profile', new ClientProfileType())
            ->add('is_accepted', 'checkbox', array(
                'required' => true,
                'property_path' => false
            ))
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::BIND, function(\Symfony\Component\Form\Event\DataEvent $event)  {
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
        return 'client_registration';
    }
}
