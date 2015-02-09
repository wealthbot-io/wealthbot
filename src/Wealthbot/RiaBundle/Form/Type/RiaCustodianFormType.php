<?php
/**
 * Created by JetBrains PhpStorm.
 * User: wealthbotdev1
 * Date: 1/14/14
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaCustodianFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('custodian', 'entity', array(
                'label' => false,
                'class' => 'Wealthbot\AdminBundle\Entity\Custodian',
                'property' => 'name',
                'expanded' => true,
            ))
            ->add('allow_non_electronically_signing', 'choice', array(
                'choices' => array(true => 'Yes', false => 'No'),
                'expanded' => true
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation'
        ));
    }

    public function getName()
    {
        return 'ria_custodian';
    }
}