<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Form\Type\RiaDocumentsFormType;
use Wealthbot\UserBundle\Manager\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('is_show_client_expected_asset_class', 'choice', array(
                'choices'  => array(1 => 'Yes', 0 => 'No'),
                'required' => false,
                'expanded' => true,
            ))
            ->add('is_show_expected_costs', 'choice', array(
                'choices'   => array(1 => 'Yes', 0 => 'No'),
                'expanded'  => true,
                'required'  => false,
            ))
            //Contracts
            ->add('documents', new RiaDocumentsFormType(), array(
                'mapped' => false
            ))
        ;

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) use ($documentManager) {
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation'
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_ria_proposals_form';
    }
}
