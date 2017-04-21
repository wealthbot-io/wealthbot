<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.09.12
 * Time: 14:38
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRetirementAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountType', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\ClientAccountType',
                'property' => 'name',
                'label' => 'Type',
            ])
            ->add('value', 'text', ['label' => 'Estimated Value'])
            ->add('monthly_contributions', 'text', ['label' => 'Estimated Monthly Contributions'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_client_retirement_account_type';
    }
}
