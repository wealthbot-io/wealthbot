<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 25.03.13
 * Time: 18:37
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FundFormType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('symbol', 'text');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\Security'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fund';
    }
}