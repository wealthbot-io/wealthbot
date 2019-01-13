<?php

namespace Wealthbot\RiaBundle\Form\Type;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Form\Type\GroupFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\Group;

class UserGroupsFormType extends GroupFormType
{
    private $class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, ['label' => 'Groups:']);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event){
            $this->class = $event->getForm()->getConfig()->getOption('class');
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
            'intention' => 'group',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user_group_form';
    }
}
