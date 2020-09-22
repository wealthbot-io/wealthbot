<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('investment_management_agreement', FileType::class, [
                'label' => 'Investment Management Agreement',
                'required' => false,
            ])
            ->add('user_agreement', FileType::class, ['label' => 'User Agreement', 'required' => false])
            ->add('privacy_policy', FileType::class, ['label' => 'Privacy Policy', 'required' => false])
            ->add('adv', FileType::class, ['label' => 'ADV', 'required' => false]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            $file = null;
            foreach ($data as $item) {
                if ($item instanceof UploadedFile) {
                    $file = $item;
                }
            }

            if (null === $file) {
                $form->addError(new FormError('No files to upload.'));
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'documents';
    }
}
