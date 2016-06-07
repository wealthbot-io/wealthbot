<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 20.03.14
 * Time: 18:47.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OneTimeDistributionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', 'money', [
            'attr' => ['class' => 'input-mini'],
            'currency' => 'USD',
            'label' => 'One Time Distribution',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\Distribution',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'one_time_distribution_form';
    }
}
