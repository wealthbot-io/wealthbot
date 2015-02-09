<?php

namespace Wealthbot\UserBundle\Form\Type;


use Wealthbot\RiaBundle\Validator\Constraint\CurrentPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UpdatePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', array(
            'mapped' => false,
            'constraints' => array(new CurrentPassword()),
        ));
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
            'intention'  => 'change_password',
        ));
    }

    public function getName()
    {
        return 'update_password';
    }
}