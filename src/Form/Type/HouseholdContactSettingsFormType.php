<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:58.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseholdContactSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, [
                'attr' => ['class' => 'input-xxlarge'],
                'label' => 'Street Address',
            ])
            ->add('city', TextType::class, [
                'attr' => ['class' => 'input-medium'],
            ])
            ->add('state', EntityType::class, [
                'attr' => ['class' => 'input-medium'],
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
            ])
            ->add('zip', TextType::class, [
                'attr' => ['class' => 'input-mini'],
            ])
            ->add('mailingStreet', TextType::class, [
                'attr' => ['class' => 'input-xxlarge'],
                'label' => 'Mailing Street',
            ])
            ->add('mailingCity', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Mailing City',
            ])
            ->add('mailingState', EntityType::class, [
                'attr' => ['class' => 'input-medium'],
                'class' => 'App\\Entity\\State',
                'label' => 'Mailing State',
                'placeholder' => 'Select a State',
            ])
            ->add('mailingZip', TextType::class, [
                'attr' => ['class' => 'input-mini'],
                'label' => 'Mailing Zip Code',
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'property_path' => 'user.email',
            ])
            ->add('phoneNumber', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Phone Number',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Profile',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_contact_settings';
    }
}
