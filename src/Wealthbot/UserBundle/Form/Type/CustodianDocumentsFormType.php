<?php

namespace Wealthbot\UserBundle\Form\Type;


use Wealthbot\AdminBundle\Entity\Custodian;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CustodianDocumentsFormType extends AbstractType
{
    private $custodian;

    public function __construct(Custodian $custodian)
    {
        $this->custodian = $custodian;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('account_disclosure', 'file', array('label' => 'Account Disclosure', 'required' => false))
            ->add('ira_account_disclosure', 'file', array('label' => 'IRA Account Disclosure', 'required' => false))
            ->add('roth_account_disclosure', 'file', array('label' => 'Roth Account Disclosure', 'required' => false));

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) {
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

    public function getName()
    {
        return 'custodian_' . $this->custodian->getId() . '_documents';
    }
}