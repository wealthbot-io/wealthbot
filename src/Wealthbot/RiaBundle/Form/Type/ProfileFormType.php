<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Wealthbot\UserBundle\Form\Type\UserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class ProfileFormType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username')->remove('plainPassword');

        $factory = $builder->getFormFactory();


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($factory){
            $data = $event->getData();
            $form = $event->getForm();


            if ($data){
                $form->add($factory->createNamed('groups', 'entity', null, array(
                    'class' => 'WealthbotUserBundle:Group',
                    'choices' => $data->getGroups(),
                    'disabled' => true,
                    'multiple' => true
                )));
            }

        });




    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'my_profile';
    }
}