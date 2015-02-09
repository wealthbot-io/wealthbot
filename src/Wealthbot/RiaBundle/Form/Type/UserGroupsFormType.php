<?php


namespace Wealthbot\RiaBundle\Form\Type;

use FOS\UserBundle\Form\Type\GroupFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
        $builder->add('name', null, array('label' => 'Groups:'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'group',
        ));
    }

    public function getName()
    {
        return 'user_group_form';
    }
}