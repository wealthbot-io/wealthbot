<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Form\Type\ProfileType;

class UserType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('profile', new ProfileType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'user';
    }
}
