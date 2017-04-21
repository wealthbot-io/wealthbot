<?php

namespace Wealthbot\RiaBundle\Form\Type;

use FOS\UserBundle\Form\Type\GroupFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\Group;

class UserGroupsFormType extends GroupFormType
{
    private $class;

    /**
     * @param string $class The Group class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, ['label' => 'Groups:']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
            'intention' => 'group',
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'user_group_form';
    }
}
