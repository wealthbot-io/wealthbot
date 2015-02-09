<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', 'text', array('required' => true))
            ->add('first_name')
            ->add('last_name')
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::BIND, function($event) {
            $form = $event->getForm();
            $data = $event->getData();

            $company = trim($data->getCompany());
            if (strlen($company) == 0) {
                $form->get('company')->addError(new \Symfony\Component\Form\FormError('Required.'));
            }

            // your form data
            $data = $event->getData();
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'wealthbot_userbundle_profiletype';
    }
}
