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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PortfolioModelFormType extends AbstractType
{
    private $parent;

    private $owner;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $owner = $this->owner;
        $parent = $this->parent;

        $builder->add('name', TextType::class);

        // Add Event - when we create a model then we will need to set owner and parent
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($owner, $parent) {
            $data = $event->getData();

            if ($data && !$data->getId()) {
                $data->setOwner($owner);
                $data->setParent($parent);
            }
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->parent = $event->getForm()->getConfig()->getOption('parent');
            $this->owner = $event->getForm()->getConfig()->getOption('owner');
        });
    }

    /**
     * @param OptionsResolverInterface $resolver
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
}
