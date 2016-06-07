<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\RiaBundle\Validator\Constraint\CurrentPassword;

class UpdatePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', [
            'mapped' => false,
            'constraints' => [new CurrentPassword()],
        ]);
        $builder->add('plainPassword', 'repeated', [
            'type' => 'password',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'intention' => 'change_password',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'update_password';
    }
}
