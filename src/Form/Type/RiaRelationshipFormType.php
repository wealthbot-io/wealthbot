<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\RiaCompanyInformation;

class RiaRelationshipFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var RiaCompanyInformation $data */
        $data = $builder->getData();



        $builder->add('relationship_type', ChoiceType::class, [
            'choices' => array_combine(array_keys(RiaCompanyInformation::$relationship_type_choices), array_values(RiaCompanyInformation::$relationship_type_choices)),
            'expanded' => true,
            'multiple' => false,
            'data' => ($data && $data->getRelationshipType() ? $data->getRelationshipType() : 0),
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_relationship_form';
    }
}
