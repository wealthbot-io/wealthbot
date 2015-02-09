<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\User;

class RiaCompanyProfileFormType extends AbstractType
{
    /** @var bool */
    private $isPreSave;

    public function __construct($isPreSave = false)
    {
        $this->isPreSave = $isPreSave;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $builder->getData();

        /** @var User $ria */
        $ria = $riaCompanyInformation->getRia();

        if ($riaCompanyInformation) {
            $name             = $riaCompanyInformation->getName() ? $riaCompanyInformation->getName() : $ria->getProfile()->getCompany();
            $primaryFirstName = $riaCompanyInformation->getPrimaryFirstName() ? $riaCompanyInformation->getPrimaryFirstName() : $ria->getProfile()->getFirstName();
            $primaryLastName  = $riaCompanyInformation->getPrimaryLastName() ? $riaCompanyInformation->getPrimaryLastName() : $ria->getProfile()->getLastName();
            $contactEmail     = $riaCompanyInformation->getContactEmail() ? $riaCompanyInformation->getContactEmail() : $ria->getEmail();
        } else {
            $name             = $ria->getProfile()->getCompany();
            $primaryFirstName = $ria->getProfile()->getFirstName();
            $primaryLastName  = $ria->getProfile()->getLastName();
            $contactEmail     = $ria->getEmail();
        }

        //Company information
        $builder
            ->add('name', 'text', array('data' => $name, 'required' => false))
            ->add('slug', 'text', array('required' => true))
            ->add('primary_first_name', 'text', array('data' => $primaryFirstName, 'required' => false))
            ->add('primary_last_name', 'text', array('data' => $primaryLastName, 'required' => false))
            ->add('website', 'url', array('required' => false))
            ->add('address', 'text', array('required' => false))
            ->add('office', 'text', array('required' => false))
            ->add('city', 'text', array('required' => false))
            ->add('state', 'entity', array(
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State',
                'required' => false
            ))
            ->add('zipcode', 'text', array('required' => false))
            ->add('phone_number', 'text', array('required' => false))
            ->add('fax_number', 'text', array('required' => false))
            ->add('contact_email', 'email', array('data' => $contactEmail, 'required' => false))
        ;

        //Other information
        $builder
            ->add('min_asset_size', 'number', array(
                'precision' => 2,
                'grouping' => true
            ))
            ->add('logo_file', 'file')
        ;
        //Proposal processing
        $builder->add('portfolio_processing', 'choice', array(
            'choices'  => RiaCompanyInformation::getPortfolioProcessingChoices(),
            'expanded' => true
        ));

        $this->addValidator($builder);

        if (!$this->isPreSave) {
            $this->addOnBindValidator($builder);
        }
    }

    protected function addValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event)  {
            $data = $event->getData();

            $phoneNum = str_replace(array(' ', '_', '-', '(', ')'), '', $data->getPhoneNumber());
            $data->setPhoneNumber($phoneNum);

            $faxNum = str_replace(array(' ', '_', '-', '(', ')'), '', $data->getFaxNumber());
            $data->setFaxNumber($faxNum);

            $zip = str_replace(array(' ', '-'), '', $data->getZipcode());
            $data->setZipcode($zip);
        });
    }

    protected function addOnBindValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event){
            $form = $event->getForm();

            /** @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

            //Company information
            $phoneDigits = 10;
            $zipDigits = 5;

            if (!$data->getName()) {
                $form->get('name')->addError(new FormError('Required.'));
            }

            if (!$data->getPrimaryFirstName()) {
                $form->get('primary_first_name')->addError(new FormError('Required.'));
            }

            if (!$data->getPrimaryLastName()) {
                $form->get('primary_last_name')->addError(new FormError('Required.'));
            }

            if (!$data->getWebsite()) {
                $form->get('website')->addError(new FormError('Required.'));
            }

            if (!$data->getAddress()) {
                $form->get('address')->addError(new FormError('Required.'));
            }

            if (!$data->getPhoneNumber()) {
                $form->get('phone_number')->addError(new FormError("Enter correct phone number."));
            } elseif (strlen($data->getPhoneNumber()) != $phoneDigits) {
                $form->get('phone_number')->addError(new FormError("Phone number must be {$phoneDigits} digits."));
            }

            if ($data->getFaxNumber() && strlen($data->getFaxNumber()) != $phoneDigits) {
                $form->get('fax_number')->addError(new FormError("Fax number must be {$phoneDigits} digits."));
            }

            if (!$data->getZipcode()) {
                $form->get('zipcode')->addError(new FormError("Enter correct zip code."));
            } elseif (strlen($data->getZipcode()) != $zipDigits) {
                $form->get('zipcode')->addError(new FormError("Zip code must be {$zipDigits} digits."));
            }

            if (!$data->getCity()) {
                $form->get('city')->addError(new FormError('Required.'));
            }

            if (!$data->getState()) {
                $form->get('state')->addError(new FormError('Required.'));
            }

            if (!$data->getContactEmail()) {
                $form->get('contact_email')->addError(new FormError('Required.'));
            }

            if (!$data->getSlug()) {
                $form->get('slug')->addError(new FormError('Required.'));
            }

            //Other information
            if (!is_numeric($data->getMinAssetSize())) {
                $form->get('min_asset_size')->addError(new FormError('Required.'));
            }

            // If ria already have logo do not show error for him
            if (!$data->getLogo() && !is_object($form->get('logo_file')->getData())) {
                $form->get('logo_file')->addError(new FormError('Required.'));
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
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
