<?php

namespace Wealthbot\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wealthbot\AdminBundle\Entity\Custodian;

class CustodianDocumentsFormType extends AbstractType
{
    private $custodian;

    public function __construct(Custodian $custodian)
    {
        $this->custodian = $custodian;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('account_disclosure', 'file', ['label' => 'Account Disclosure', 'required' => false])
            ->add('ira_account_disclosure', 'file', ['label' => 'IRA Account Disclosure', 'required' => false])
            ->add('roth_account_disclosure', 'file', ['label' => 'Roth Account Disclosure', 'required' => false]);

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
        return 'custodian_'.$this->custodian->getId().'_documents';
    }
}
