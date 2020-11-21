<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Document;

class DocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class)
            ->add('type', HiddenType::class);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data instanceof Document && Document::TYPE_ADV === $data->getType() && 'application/pdf' !== $data->getMimeType()) {
            $form->get('file')->addError(new FormError('Only pdf files are allowed'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Document',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'document';
    }
}
