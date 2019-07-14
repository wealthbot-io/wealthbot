<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, ['required' => true])
            ->add('first_name')
            ->add('last_name')
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function ($event) {
            $form = $event->getForm();
            $data = $event->getData();

            $company = trim($data->getCompany());
            if (0 === strlen($company)) {
                $form->get('company')->addError(new \Symfony\Component\Form\FormError('Required.'));
            }

            // your form data
            $data = $event->getData();
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Profile'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_profiletype';
    }
}
