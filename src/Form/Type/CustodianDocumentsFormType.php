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


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->custodian = $options['custodian'];

        $builder->add('account_disclosure', FileType::class, [
            'label' => 'Account Disclosure',
            'required' => false,
            'mapped' => false
        ])
            ->add('ira_account_disclosure', FileType::class, [
                'label' => 'IRA Account Disclosure',
                'required' => false,
                'mapped' => false
            ])
            ->add('roth_account_disclosure', FileType::class, [
                'label' => 'Roth Account Disclosure',
                'required' => false,
                'mapped' => false
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $files = [];
            foreach ($data as $item) {
                if ($item instanceof UploadedFile) {
                    $files[] = $item;
                }
            }
            $form->setData($files);
        });
    }

    public function getBlockPrefix()
    {
        return 'custodian_documents';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'custodian' => null,
            'data_class' => null
        ]);
    }
}
