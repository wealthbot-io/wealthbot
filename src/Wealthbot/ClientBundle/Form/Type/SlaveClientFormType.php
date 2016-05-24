<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.07.13
 * Time: 17:46
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\User;

class SlaveClientFormType extends RegistrationFormType
{
    public function __construct()
    {
        parent::__construct('Wealthbot\UserBundle\Entity\User');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username');
        $builder->remove('plainPassword');
        $builder->add('profile', new SlaveClientProfileFormType());

        $factory = $builder->getFormFactory();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            $form = $event->getForm();
            $user = $event->getData();

            $access = 'limited';

            if ($user && $user->hasRole('ROLE_CLIENT_FULL')) {
                $access = 'full';
            }

            $form->add($factory->createNamed('access', 'choice', $access, [
                'choices' => ['full' => 'Full', 'limited' => 'Limited'],
                'mapped' => false,
                'auto_initialize' => false,
            ]));
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();

            $access = $form->get('access')->getData();

            if ($user->hasRole('ROLE_CLIENT_FULL') && $access === 'limited') {
                $user->removeRole('ROLE_CLIENT_FULL');
            }

            if (!$user->hasRole('ROLE_CLIENT_FULL') && $access === 'full') {
                $user->addRole('ROLE_CLIENT_FULL');
            }

            /* @var User $user */
            $user->setUsername($user->getEmail());
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'intention' => 'registration',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'slave_client';
    }
}
