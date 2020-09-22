<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Document;

class RiaDocumentsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(Document::TYPE_USER_AGREEMENT, FileType::class, [
                'label' => 'User Agreement',
                'required' => true,
                'attr' => [
                    'accept' => '.pdf',
                ],
            ])
            ->add(Document::TYPE_ADV, FileType::class, [
                'label' => 'ADV',
                'required' => true,
                'attr' => [
                    'accept' => '.pdf',
                ],
            ])
            ->add(Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT, FileType::class, [
                'label' => 'Investment Management Agreement',
                'required' => true,
                'attr' => [
                    'accept' => '.pdf',
                ],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            foreach ($data as $key => $item) {
                if ($item instanceof UploadedFile) {
                    if (!$item->isValid()) {
                        $form->get($key)->addError(new FormError('File uploading error code'.$item->getError()));
                    } elseif (Document::TYPE_USER_AGREEMENT === $key && 'application/pdf' !== $item->getMimeType()) {
                        $form->get(Document::TYPE_USER_AGREEMENT)->addError(new FormError('Only pdf files are allowed'));
                    } elseif (Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT === $key && 'application/pdf' !== $item->getMimeType()) {
                        $form->get(Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT)->addError(new FormError('Only pdf files are allowed'));
                    } elseif (Document::TYPE_ADV === $key && 'application/pdf' !== $item->getMimeType()) {
                        $form->get(Document::TYPE_ADV)->addError(new FormError('Only pdf files are allowed'));
                    }
                }
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'ria_documents';
    }
}
