<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 12:03
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubclassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['label' => 'Subclass'])
            ->add('expected_performance', 'text', ['label' => 'Expected Performance (%)'])
            ->add('assetClass', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\AssetClass',
                'placeholder' => 'Choose Asset Class',
            ])
            ->add('accountType')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\Subclass',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_subclass_type';
    }
}
