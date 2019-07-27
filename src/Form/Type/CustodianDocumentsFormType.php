<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Custodian;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustodianDocumentsFormType extends AbstractType
{
    private $custodian;

    private $cid;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->custodian = $options['custodian'];
        $this->cid = $this->custodian->getId();

        $builder->add('account_disclosure', FileType::class, [
            'label' => 'Account Disclosure',
            'required' => false
        ])
            ->add('ira_account_disclosure', FileType::class, [
                'label' => 'IRA Account Disclosure',
                'required' => false
            ])
            ->add('roth_account_disclosure', FileType::class, [
                'label' => 'Roth Account Disclosure',
                'required' => false
            ]);
    }

    public function getBlockPrefix()
    {
        return 'custodian_documents_'.$this->cid;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'custodian' => null,
            'data_class' => null,
            'csrf_protection' => false
        ]);
    }
}
