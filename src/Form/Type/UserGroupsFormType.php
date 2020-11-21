<?php

namespace App\Form\Type;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Form\Type\GroupFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserGroupsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            null,
            [
            'label' => 'Group Name:',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Group'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user_group_form';
    }
}
