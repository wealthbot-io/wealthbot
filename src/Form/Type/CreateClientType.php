<?php

namespace App\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateClientType extends BaseType
{
    private $ria;
    private $class;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $this->class = $options['class'];

        $data = $builder->getData();


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
        ;
        $builder
            ->add('profile', CreateClientProfileType::class)
        ;

        $plainPassword = $this->generatePlainPassword();

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($plainPassword) {
            $user = $event->getData();
            $user->setUsername($user->getEmail());
            $user->setPlainPassword($plainPassword);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'ria' => null,
            'class' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_riacreateclienttype';
    }

    private function generatePlainPassword($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; ++$i) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
}
