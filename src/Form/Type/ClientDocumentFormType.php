<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use App\Entity\Document;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientDocumentFormType extends DocumentFormType
{
    /** @var $isRiaClientView bool */
    private $isRiaClientView;

    protected $allowedMimeTypes = [
        '.pdf' => 'application/pdf', //.pdf
        '.doc' => 'application/msword', //.doc
        '.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', //.docx
        '.csv' => 'text/csv', //.csv
        '.xls' => 'application/vnd.ms-excel', //.xls
        '.xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', //.xlsx
        '.jpeg' => 'image/jpeg', //.jpeg
        '.png' => 'image/png', //.png
        '.gif' => 'image/gif', //.gif
        '.txt' => 'text/plain', //.txt
        '.ppt' => 'application/vnd.ms-powerpoint', //.ppt
        '.pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', //.pptx
        '.gdoc' => 'application/vnd.google-apps.document', //.gdoc
        '.gslides' => 'application/vnd.google-apps.presentation', //.gslides
        '.gsheet' => 'application/vnd.google-apps.spreadsheet', //.gsheet
        '.gdraw' => 'application/vnd.google-apps.drawing', //.gdraw
        '.gtable' => 'application/vnd.google-apps.fusiontable', //.gtable
        '.gform' => 'application/vnd.google-apps.form', //.gform
    ];
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isRiaClientView = $options['is_client_view'];

        parent::buildForm($builder, $options);
        $builder->remove('type');

        if ($this->isRiaClientView) {
            $builder
                ->add('is_client_notified', 'checkbox', [
                    'label' => 'Notify client by email a document has been uploaded',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('is_for_all_clients', 'checkbox', [
                    'label' => 'Upload to all clients',
                    'mapped' => false,
                    'required' => false,
                ])
            ;
        }
    }

    public function onBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();


        if ($data instanceof Document && !in_array($data->getMimeType(), $this->allowedMimeTypes)) {
            $formats = array_keys($this->allowedMimeTypes);
            $form->get('file')->addError(new FormError(sprintf('Only following file formats are allowed: %s', implode(', ', $formats))));
        }
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Document',
            'is_client_view' => null,
            'csrf_protection' => false
        ]);
    }
}
