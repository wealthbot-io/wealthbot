<?php

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('profile', ProfileType::class);
        $builder->remove('username')->remove('plainPassword');

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data) {
                $form->add($factory->createNamed('groups', EntityType::class, null, [
                    'class' => 'App\\Entity\\Group',
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
            'data_class' => 'App\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'my_profile';
    }
}
