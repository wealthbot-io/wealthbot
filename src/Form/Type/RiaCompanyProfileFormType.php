<?php

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

class RiaCompanyProfileFormType extends AbstractType
{
    /** @var bool */
    private $isPreSave;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isPreSave = $options['isPreSave'];

        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $builder->getData();

        /** @var User $ria */
        $ria = $riaCompanyInformation->getRia();

        if ($riaCompanyInformation) {
            $name = $riaCompanyInformation->getName() ? $riaCompanyInformation->getName() : $ria->getProfile()->getCompany();
            $primaryFirstName = $riaCompanyInformation->getPrimaryFirstName() ? $riaCompanyInformation->getPrimaryFirstName() : $ria->getProfile()->getFirstName();
            $primaryLastName = $riaCompanyInformation->getPrimaryLastName() ? $riaCompanyInformation->getPrimaryLastName() : $ria->getProfile()->getLastName();
            $contactEmail = $riaCompanyInformation->getContactEmail() ? $riaCompanyInformation->getContactEmail() : $ria->getEmail();
        } else {
            $name = $ria->getProfile()->getCompany();
            $primaryFirstName = $ria->getProfile()->getFirstName();
            $primaryLastName = $ria->getProfile()->getLastName();
            $contactEmail = $ria->getEmail();
        }

        //Company information
        $builder
            ->add('name', TextType::class, [
                'data' => $name,
                'required' => false,
                'label' => 'Company Name:'
            ])
            ->add('slug', TextType::class, ['required' => true,
                'label' => 'Your Custom URL',
                'attr' => [
                      //  'placeholder' => 'custom-url.wealthbot.io'
                ],
                'help_html'=> true,
                'help' => '<span class="help-block ria-intake-help">(No spaces or symbols allowed)</span>'


            ])
            ->add('primary_first_name', TextType::class, ['data' => $primaryFirstName, 'required' => false])
            ->add('primary_last_name', TextType::class, ['data' => $primaryLastName, 'required' => false])
            ->add('website', UrlType::class, ['required' => false])
            ->add('address', TextType::class, ['required' => false])
            ->add('office', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('state', EntityType::class, [
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('zipcode', TextType::class, ['required' => false, 'attr'=>[
                'placeholder' => '00000'
            ]])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('fax_number', TextType::class, ['required' => false])
            ->add('contact_email', EmailType::class, ['data' => $contactEmail, 'required' => false])
        ;

        //Other information
        $builder
            ->add('min_asset_size', NumberType::class, [
                'scale' => 2,
                'grouping' => true,
            ])
            ->add('logo_file', FileType::class)
        ;
        //Proposal processing
        $builder->add('portfolio_processing', ChoiceType::class, [
            'choices' => RiaCompanyInformation::getPortfolioProcessingChoices(),
            'expanded' => true,
        ]);

        $this->addValidator($builder);

        if (!$this->isPreSave) {
            $this->addOnSubmitValidator($builder);
        }
    }

    protected function addValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            $phoneNum = str_replace([' ', '_', '-', '(', ')'], '', $data->getPhoneNumber());
            $data->setPhoneNumber($phoneNum);

            $faxNum = str_replace([' ', '_', '-', '(', ')'], '', $data->getFaxNumber());
            $data->setFaxNumber($faxNum);

            $zip = str_replace([' ', '-'], '', $data->getZipcode());
            $data->setZipcode($zip);
        });
    }

    protected function addOnSubmitValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @param \App\Entity\RiaCompanyInformation $data */
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
                $form->get('phone_number')->addError(new FormError('Enter correct phone number.'));
            } elseif (strlen($data->getPhoneNumber()) !== $phoneDigits) {
                $form->get('phone_number')->addError(new FormError("Phone number must be {$phoneDigits} digits."));
            }

            if ($data->getFaxNumber() && strlen($data->getFaxNumber()) !== $phoneDigits) {
                $form->get('fax_number')->addError(new FormError("Fax number must be {$phoneDigits} digits."));
            }

            if (!$data->getZipcode()) {
                $form->get('zipcode')->addError(new FormError('Enter correct zip code.'));
            } elseif (strlen($data->getZipcode()) !== $zipDigits) {
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
            'isPreSave' => null,
            'error_bubbling' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
