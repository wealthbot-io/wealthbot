<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.09.12
 * Time: 14:32
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\ClientAdditionalContact;
use Wealthbot\ClientBundle\Form\Validator\ClientSpouseFormValidator;
use Wealthbot\UserBundle\Entity\Profile;

class ClientProfileFormType extends AbstractType
{
    /** @var bool $isPreSave */
    private $isPreSave;

    public function __construct($isPreSave = false)
    {
        $this->isPreSave = $isPreSave;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $data Profile */
        $data = $builder->getData();
        $formFactory = $builder->getFormFactory();

        $isExist = $data->getId();

        $builder
            ->add('first_name', 'text', [
                'required' => false,
            ])
            ->add('middle_name', 'text', [
                'required' => false,
            ])
            ->add('last_name', 'text', [
                'required' => false,
            ])
            ->add('street', 'text', ['required' => false])
            ->add('city', 'text', ['required' => false])
            ->add('state', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => true,
            ])
            ->add('zip', 'text', ['required' => false])
            ->add('is_different_address', 'checkbox', [
                'label' => 'Is your mailing address different than the one above?',
                'required' => false,
            ])
            ->add('mailing_street', 'text', ['required' => false])
            ->add('mailing_city', 'text', ['required' => false])
            ->add('mailingState', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'Mailing state',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('mailing_zip', 'text', ['required' => false])
            ->add('birth_date', 'date', [
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',
                'required' => true,
                'attr' => ['class' => 'jq-date input-small'],
            ])
            ->add('phone_number', 'text', ['required' => false])
            ->add('citizenship', 'choice', [
                'choices' => [
                    1 => 'Yes',
                    0 => 'No',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'mapped' => false,
                'data' => $isExist ? 1 : null,
                'label' => ($data && $data->getMaritalStatus() === 'Married' ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
            ])
            ->add('marital_status', 'choice', [
                'choices' => Profile::getMaritalStatusChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('spouse', new ClientSpouseFormType(), [
                'property_path' => 'user.spouse',
            ])
            ->add('annual_income', 'choice', [
                'choices' => Profile::getAnnualIncomeChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('estimated_income_tax', 'percent', [
                'precision' => 0,
                'required' => false,
                'label' => 'What is your estimated income tax bracket?',
            ])
            ->add('liquid_net_worth', 'choice', [
                'choices' => Profile::getLiquidNetWorthChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('employment_type', 'choice', [
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formFactory) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === null) {
                return;
            }

            if (isset($data['marital_status'])) {
                $form->remove('citizenship');
                $form->add($formFactory->createNamed(
                    'citizenship',
                    'choice',
                    null,
                    [
                        'choices' => [
                            1 => 'Yes',
                            0 => 'No',
                        ],
                        'expanded' => true,
                        'multiple' => false,
                        'required' => false,
                        'auto_initialize' => false,
                        'data' => (isset($data['id']) ? 1 : null),
                        'label' => ($data['marital_status'] === 'Married' ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
                    ])
                );
            }
        });

        if (!$this->isPreSave) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validate']);
        }

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validatePreSave']);
    }

    public function validate(FormEvent $event)
    {
        /** @var \Wealthbot\UserBundle\Entity\Profile $data */
        $data = $event->getData();
        $form = $event->getForm();

        if ($form->has('first_name') && !$data->getFirstName()) {
            $form->get('first_name')->addError(new FormError('Required.'));
        }
        if ($form->has('last_name') && !$data->getLastName()) {
            $form->get('last_name')->addError(new FormError('Required.'));
        }
        if ($form->has('street') && !$data->getStreet()) {
            $form->get('street')->addError(new FormError('Required.'));
        }
        if ($form->has('city') && !$data->getCity()) {
            $form->get('city')->addError(new FormError('Required.'));
        }
        if ($form->has('state') && !$data->getState()) {
            $form->get('state')->addError(new FormError('Required.'));
        }
        if ($form->has('estimated_income_tax') && !$data->getEstimatedIncomeTax()) {
            $form->get('estimated_income_tax')->addError(new FormError('Required.'));
        }

        // Choices validation
        if ($form->has('marital_status') && !in_array($data->getMaritalStatus(), Profile::getMaritalStatusChoices())) {
            $form->get('marital_status')->addError(new FormError('Required.'));
        }
        if ($form->has('annual_income') && !in_array($data->getAnnualIncome(), Profile::getAnnualIncomeChoices())) {
            $form->get('annual_income')->addError(new FormError('Required.'));
        }
        if ($form->has('liquid_net_worth') && !in_array($data->getLiquidNetWorth(), array_keys(Profile::getLiquidNetWorthChoices()))) {
            $form->get('liquid_net_worth')->addError(new FormError('Required.'));
        }
        if ($form->has('employment_type') && !in_array($data->getEmploymentType(), array_keys(Profile::getEmploymentTypeChoices()))) {
            $form->get('employment_type')->addError(new FormError('Required.'));
        }

        if ($form->has('birth_date')) {
            $birthDateData = $form->get('birth_date')->getData();

            if ($birthDateData && $birthDateData instanceof \DateTime) {
                $year = (int) $birthDateData->format('Y');

                if ($year < 1900) {
                    $form->get('birth_date')->addError(new FormError('year must start with 19 or 20 e.g. 1980'));
                }
            } else {
                $form->get('birth_date')->addError(new FormError('date format must be MM-DD-YYYY'));
            }
        }

        if ($form->has('spouse') && $data->getMaritalStatus() === Profile::CLIENT_MARITAL_STATUS_MARRIED) {
            /** @var ClientAdditionalContact $spouse */
            $spouse = $form->get('spouse')->getData();
            $phoneNum = str_replace([' ', '-', '(', ')'], '', $data->getPhoneNumber());

            $spouseValidator = new ClientSpouseFormValidator($form->get('spouse'), $data->getUser()->getSpouse());
            $spouseValidator->validate();

            $spouse->setSpouseFirstName($data->getFirstName());
            $spouse->setSpouseMiddleName($data->getMiddleName());
            $spouse->setSpouseLastName($data->getLastName());
            $spouse->setSpouseBirthDate($data->getBirthDate());
            $spouse->setState($data->getState());
            $spouse->setCity($data->getCity());
            $spouse->setStreet($data->getStreet());
            $spouse->setZip($data->getZip());
            $spouse->setPhoneNumber($phoneNum);
            $spouse->setType(ClientAdditionalContact::TYPE_SPOUSE);
            $spouse->setMaritalStatus(Profile::CLIENT_MARITAL_STATUS_MARRIED);
        }
    }

    public function validatePreSave(FormEvent $event)
    {
        $isPreSave = $this->isPreSave;

        $form = $event->getForm();

        /** @var $clientProfile Profile */
        $clientProfile = $event->getData();
        $citizenship = $form->has('citizenship') ? $form->get('citizenship') : null;
        $minYears = 18;
        $phoneDigits = 10;
        $zipDigits = 5;
        $nowDate = new \DateTime('now');

        $phoneNum = str_replace([' ', '-', '(', ')'], '', $clientProfile->getPhoneNumber());
        $zip = str_replace([' ', '-'], '', $clientProfile->getZip());
        $mailingZip = str_replace([' ', '-'], '', $clientProfile->getMailingZip());

        if (!$isPreSave) {
            if ($citizenship && (is_null($citizenship->getData()) || (int) $citizenship->getData() === 0)) {
                $citizenship->addError(new FormError('You must be US citizen or resident.'));
            }

            if ($form->has('phone_number') && !is_numeric($phoneNum)) {
                $form->get('phone_number')->addError(new FormError('Enter correct phone number.'));
            } elseif ($form->has('phone_number') && strlen($phoneNum) !== $phoneDigits) {
                $form->get('phone_number')->addError(new FormError("Phone number must be {$phoneDigits} digits."));
            }

            if ($form->has('zip') && !is_numeric($zip)) {
                $form->get('zip')->addError(new FormError('Enter correct zip code.'));
            } elseif ($form->has('zip') && strlen($zip) !== $zipDigits) {
                $form->get('zip')->addError(new FormError("Zip code must be {$zipDigits} digits."));
            } elseif ($form->has('zip')) {
                $clientProfile->setZip($zip);
            }

            if ($mailingZip) {
                if ($form->has('mailing_zip') && !is_numeric($mailingZip)) {
                    $form->get('mailing_zip')->addError(new FormError('Enter correct zip code.'));
                } elseif ($form->has('mailing_zip') && strlen($zip) !== $zipDigits) {
                    $form->get('mailing_zip')->addError(new FormError("Zip code must be {$zipDigits} digits."));
                } elseif ($form->has('mailing_zip')) {
                    $clientProfile->setMailingZip($zip);
                }
            }

            if ($form->has('birth_date')) {
                $birthDate = $clientProfile->getBirthDate();
                if ($birthDate) {
                    $interval = $nowDate->diff($birthDate);
                    if ((int) $interval->format('%y%') < $minYears) {
                        $form->get('birth_date')->addError(new FormError("You must be at least {$minYears} years old."));
                    }
                }
            }
        }

        if (!$clientProfile->getIsDifferentAddress()) {
            $clientProfile->setMailingCity(null);
            $clientProfile->setMailingState(null);
            $clientProfile->setMailingStreet(null);
            $clientProfile->setMailingZip(null);
        }

        $clientProfile->setPhoneNumber($phoneNum);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_client_bundle_profile_type';
    }
}
