<?php

namespace Wealthbot\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\Profile;

class UserType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('profile', new ProfileType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user';
    }
}
