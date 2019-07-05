<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Validator\Constraint\CurrentPassword;

class UpdatePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', PasswordType::class, [
            'mapped' => false,
            'constraints' => [new \App\Validator\Constraints\CurrentPassword()],
        ]);
        $builder->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'intention' => 'change_password',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'update_password';
    }
}
