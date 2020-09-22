<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use App\Form\Type\AdminProfileType;
use App\Form\Type\UserType;

class CreateAdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var $data \Entity\User */
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
        };



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
            ->add('profile', AdminProfileType::class)
            ->add('level', ChoiceType::class, [
                 'choices' => $choices,
                'mapped' => false,
                'preferred_choices' => $level ? [$level] : [],
            ])
        ;

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
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
