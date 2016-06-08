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
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaCustodianFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('custodian', 'entity', [
                'label' => false,
                'class' => 'Wealthbot\AdminBundle\Entity\Custodian',
                'property' => 'name',
                'expanded' => true,
            ])
            ->add('allow_non_electronically_signing', 'choice', [
                'choices' => [true => 'Yes', false => 'No'],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_custodian';
    }
}
