<?php
/**
 * Created by JetBrains PhpStorm.
 * User: wealthbotdev1
 * Date: 1/14/14
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaCustodianFormType extends AbstractType
{
    private $ria;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $data1 = $this->ria ? $this->ria->getCustodian() :null;
        $data2 = $this->ria ? $this->ria->getAllowNonElectronicallySigning() : false;


        $builder
            ->add('custodian', EntityType::class, [
                'label' => false,
                'class' => "App\\Entity\\Custodian",
                'property_path' => 'name',
                'id_reader' => 'id',
                'mapped' => true,
                'data'=> $data1
            ])
            ->add('allow_non_electronically_signing', ChoiceType::class, [
               'choices' => [ 'Yes'=> true, 'No'=> false ],
                'expanded' =>false,
                'data'=> $data2
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
            'ria' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_custodian';
    }
}
