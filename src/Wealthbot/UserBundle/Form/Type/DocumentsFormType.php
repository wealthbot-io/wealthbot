<?php

namespace Wealthbot\UserBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('investment_management_agreement', 'file', array(
                'label' => 'Investment Management Agreement',
                'required' => false
            ))
            ->add('user_agreement', 'file', array('label' => 'User Agreement', 'required' => false))
            ->add('privacy_policy', 'file', array('label' => 'Privacy Policy', 'required' => false))
            ->add('adv', 'file', array('label' => 'ADV', 'required' => false));

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
        return 'documents';
    }

}