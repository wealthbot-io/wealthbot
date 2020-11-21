<?php

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\RiaCompanyInformation;

class RiaCompanyInformationType extends AbstractType
{
    /** @var \FOS\UserBundle\Model\User */
    private $user;

    /** @var bool */
    private $isPreSave;



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->isPreSave = $options['isPreSave'];


        /** @param \App\Entity\RiaCompanyInformation $info */
        $info = $this->user->getRiaCompanyInformation();

        if ($info) {
            $name = $info->getName() ? $info->getName() : $this->user->getProfile()->getCompany();
            $primaryFirstName = $info->getPrimaryFirstName() ? $info->getPrimaryFirstName() : $this->user->getProfile()->getFirstName();
            $primaryLastName = $info->getPrimaryLastName() ? $info->getPrimaryLastName() : $this->user->getProfile()->getLastName();
            $contactEmail = $info->getContactEmail() ? $info->getContactEmail() : $this->user->getEmail();
        } else {
            $name = $this->user->getProfile()->getCompany();
            $primaryFirstName = $this->user->getProfile()->getFirstName();
            $primaryLastName = $this->user->getProfile()->getLastName();
            $contactEmail = $this->user->getEmail();
        }

        $builder
            ->add('name', TextType::class, ['data' => $name, 'required' => false])
            ->add('slug', TextType::class, ['required' => true])
            ->add('custodianKey', TextType::class, [
                'required' => true
            ])
            ->add('custodianSecret', TextType::class, [
                'required' => true
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
            ->add('zipcode', TextType::class, ['required' => false])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('fax_number', TextType::class, ['required' => false])
            ->add('contact_email', EmailType::class, ['data' => $contactEmail, 'required' => false])
        ;

        //Other information
        $builder
            ->add('min_asset_size', MoneyType::class, [
                'scale' => 2,
                'grouping' => true,
                'required' => false,
                'currency' => 'USD'
            ])
            ->add('logo_file', FileType::class, ['required' => false])
        ;
        //Proposal processing
        $builder->add('portfolio_processing', ChoiceType::class, [
            'choices' => RiaCompanyInformation::getPortfolioProcessingChoices(),
            'disabled' => true,
            'required' => true,
            'expanded' => true,
        ]);

        $this->addValidator($builder);

        if (!$this->isPreSave) {
            $this->addOnSubmitValidator($builder);
        }
    }

    protected function addValidator(FormBuilderInterface $builder)
    {
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function (FormEvent $event) {
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
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function (\Symfony\Component\Form\FormEvent $event) {
            $form = $event->getForm();

            /** @param \App\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

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
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\RiaCompanyInformation',
            'user' => null,
            'isPreSave' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'riacompanyinformationtype';
    }
}
