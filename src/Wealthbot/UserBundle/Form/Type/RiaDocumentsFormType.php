<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wealthbot\UserBundle\Entity\Document;

class RiaDocumentsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(Document::TYPE_ADV, 'file', [
                'label' => 'ADV',
                'required' => false,
                'attr' => [
                    'accept' => '.pdf',
                ],
            ])
            ->add(Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT, 'file', [
                'label' => 'Investment Management Agreement',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            foreach ($data as $key => $item) {
                if ($item instanceof UploadedFile) {
                    if (!$item->isValid()) {
                        $form->get($key)->addError(new FormError('File uploading error code'.$item->getError()));
                    } elseif ($key === Document::TYPE_ADV && $item->getMimeType() !== 'application/pdf') {
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
