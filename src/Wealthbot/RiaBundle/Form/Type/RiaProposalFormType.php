<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Form\Type\RiaDocumentsFormType;
use Wealthbot\UserBundle\Manager\DocumentManager;

class RiaProposalFormType extends AbstractType
{
    protected $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $documentManager = $this->documentManager;

        $builder
            //Performance assumption
            ->add('is_show_client_expected_asset_class', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'required' => false,
                'expanded' => true,
            ])
            ->add('is_show_expected_costs', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
                'required' => false,
            ])
            //Contracts
            ->add('documents', new RiaDocumentsFormType(), [
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($documentManager) {
            $form = $event->getForm();
            /** @var RiaCompanyInformation $data */
            $data = $event->getData();
            $ria = $data->getRia();

            $documents = $form->get('documents')->getData();

            foreach ($documents as $key => $file) {
                if ($documentManager->getUserDocumentByType($ria->getId(), $key)) {
                    continue;
                }

                if (!($file instanceof UploadedFile)) {
                    $form->get('documents')->get($key)->addError(new FormError('Required.'));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_ria_proposals_form';
    }
}
