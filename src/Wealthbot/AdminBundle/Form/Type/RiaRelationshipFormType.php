<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;

class RiaRelationshipFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var RiaCompanyInformation $data */
        $data = $builder->getData();

        $builder->add('relationship_type', 'choice', [
            'choices' => RiaCompanyInformation::$relationship_type_choices,
            'expanded' => true,
            'multiple' => false,
            'data' => ($data && $data->getRelationshipType() ? $data->getRelationshipType() : 0),
        ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ria_relationship_form';
    }
}
