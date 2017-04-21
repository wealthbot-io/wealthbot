<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Form\Type\AdminProfileType;
use Wealthbot\UserBundle\Form\Type\UserType;

class CreateAdminUserType extends UserType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var $data \Wealthbot\UserBundle\Entity\User */
        $data = $builder->getData();

        $choices = ['master' => 'Master', 'pm' => 'Manager', 'csr' => 'CSR'];
        $level = null;

        $options['validation_groups'] = ['password'];

        if (!is_null($data) && $data->getId()) {
            foreach ($choices as $key => $choice) {
                $role = 'ROLE_ADMIN_'.strtoupper($key);
                if ($data->hasRole($role)) {
                    $level = $key;
                    break;
                }
            }
        }

        $builder
            ->remove('username')
            ->add('profile', new AdminProfileType())
            ->add('level', 'choice', [
                'choices' => $choices,
                'mapped' => false,
                'preferred_choices' => $level ? [$level] : [],
            ])
        ;

        if (!is_null($data) && $data->getId()) {
            $builder
                ->remove('email')
                ->remove('plainPassword')
                ->add('plainPassword', 'repeated', [
                    'type' => 'password',
                    'options' => ['translation_domain' => 'FOSUserBundle'],
                    'first_options' => ['label' => 'form.password'],
                    'second_options' => ['label' => 'form.password_confirmation'],
                    'required' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var $user User */
            $user = $event->getData();
            $form = $event->getForm();

            if (!$user->getId()) {
                $user->setEnabled(true);
            }

            $level = $form->get('level')->getData();

            if (!$form->get('profile')->get('first_name')->getData()) {
                $form->get('profile')->get('first_name')->addError(new FormError('Required.'));
            }

            if (!$form->get('profile')->get('last_name')->getData()) {
                $form->get('profile')->get('last_name')->addError(new FormError('Required.'));
            }

            switch ($level) {
                case 'master':
                    $user->setRoles(['ROLE_ADMIN_MASTER']);
                    break;
                case 'pm':
                    $user->setRoles(['ROLE_ADMIN_PM']);
                    break;
                case 'csr':
                    $user->setRoles(['ROLE_ADMIN_CSR']);
                    break;
            }

            $user->setUsername($user->getEmail());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($data->getId()) {
                    return ['password'];
                } else {
                    return ['Registration', 'password'];
                }
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'create_admin_user';
    }
}
