<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.10.12
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParentCeModelFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
//        $this->subscribe($builder);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\CeModel',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'strategy';
    }

    //TODO Should be removed in feature (not using)
    protected function subscribe(FormBuilderInterface $builder)
    {
        //        $subscriber = new ParentCeModelFormTypeEventsListener();
//        $builder->addEventSubscriber($subscriber);
    }
}
