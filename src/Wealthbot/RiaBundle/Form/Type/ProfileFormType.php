<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Form\Type\UserType;

class ProfileFormType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('username')->remove('plainPassword');

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data) {
                $form->add($factory->createNamed('groups', 'entity', null, [
                    'class' => 'Wealthbot\\UserBundle\\Entity\\Group',
                    'choices' => $data->getGroups(),
                    'disabled' => true,
                    'multiple' => true,
                    'auto_initialize' => false,
                ]));
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'my_profile';
    }
}
