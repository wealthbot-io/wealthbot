<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:58.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseholdContactSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', 'text', [
                'attr' => ['class' => 'input-xxlarge'],
                'label' => 'Street Address',
            ])
            ->add('city', 'text', [
                'attr' => ['class' => 'input-medium'],
            ])
            ->add('state', 'entity', [
                'attr' => ['class' => 'input-medium'],
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
            ])
            ->add('zip', 'text', [
                'attr' => ['class' => 'input-mini'],
            ])
            ->add('mailingStreet', 'text', [
                'attr' => ['class' => 'input-xxlarge'],
                'label' => 'Mailing Street',
            ])
            ->add('mailingCity', 'text', [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Mailing City',
            ])
            ->add('mailingState', 'entity', [
                'attr' => ['class' => 'input-medium'],
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'Mailing State',
                'placeholder' => 'Select a State',
            ])
            ->add('mailingZip', 'text', [
                'attr' => ['class' => 'input-mini'],
                'label' => 'Mailing Zip Code',
            ])
            ->add('email', 'text', [
                'attr' => ['class' => 'input-medium'],
                'property_path' => 'user.email',
            ])
            ->add('phoneNumber', 'text', [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Phone Number',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_contact_settings';
    }
}
