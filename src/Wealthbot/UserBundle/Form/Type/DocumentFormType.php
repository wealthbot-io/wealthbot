<?php

namespace Wealthbot\UserBundle\Form\Type;


use Wealthbot\UserBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file')
            ->add('type', 'hidden');

        $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
    }

    public function onBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data instanceof Document && $data->getType() == Document::TYPE_ADV && $data->getMimeType() != 'application/pdf') {
            $form->get('file')->addError(new FormError('Only pdf files are allowed'));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\Document'
        ));
    }

    public function getName()
    {
        return 'document';
    }
}