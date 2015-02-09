<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RiaRelationshipFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var RiaCompanyInformation $data */
        $data = $builder->getData();

        $builder->add('relationship_type', 'choice', array(
            'choices' => RiaCompanyInformation::$relationship_type_choices,
            'expanded' => true,
            'multiple' => false,
            'data' => ($data && $data->getRelationshipType() ? $data->getRelationshipType() : 0)
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation'
        ));
    }

    public function getName()
    {
        return 'ria_relationship_form';
    }
}
