<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:58
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HouseholdContactSettingsFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', 'text', array(
                'attr' => array('class' => 'input-xxlarge'),
                'label' => 'Street Address'
            ))
            ->add('city', 'text', array(
                'attr' => array('class' => 'input-medium')
            ))
            ->add('state', 'entity', array(
                'attr' => array('class' => 'input-medium'),
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State'
            ))
            ->add('zip', 'text', array(
                'attr' => array('class' => 'input-mini')
            ))
            ->add('mailingStreet', 'text', array(
                'attr' => array('class' => 'input-xxlarge'),
                'label' => 'Mailing Street'
            ))
            ->add('mailingCity', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Mailing City'
            ))
            ->add('mailingState', 'entity', array(
                'attr' => array('class' => 'input-medium'),
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'Mailing State',
                'empty_value' => 'Select a State'
            ))
            ->add('mailingZip', 'text', array(
                'attr' => array('class' => 'input-mini'),
                'label' => 'Mailing Zip Code'
            ))
            ->add('email', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'property_path' => 'user.email'
            ))
            ->add('phoneNumber', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Phone Number'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'client_contact_settings';
    }
}
