<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.02.13
 * Time: 13:43
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Wealthbot\ClientBundle\Entity\PersonalInformation;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\UserBundle\Entity\Profile;

class AccountOwnerPersonalInformationFormType extends AbstractType
{
    private $class;
    private $isPreSaved;
    private $withMaritalStatus;

    public function __construct(AccountOwnerInterface $owner, $isPreSaved = false, $withMaritalStatus = false)
    {
        $this->class = get_class($owner);
        $this->isPreSaved = $isPreSaved;
        $this->withMaritalStatus = $withMaritalStatus;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // SST_TIN fields always must be blank in initial form
        $builder
            ->add('citezen', 'choice', [
                'label' => 'Citizenship',
                'choices' => ['us' => 'United States', 'other' => 'Other'],
                'mapped' => false,
                'data' => 'us',
            ])
            ->add('ssn_tin_1', 'text', [
                'mapped' => false,
                'data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be number.']),
                    new Length([
                        'min' => 3,
                        'max' => 3,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
                ],
            ])
            ->add('ssn_tin_2', 'text', [
                'mapped' => false,
                'data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be number.']),
                    new Length([
                        'min' => 2,
                        'max' => 2,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
                ],
            ])
            ->add('ssn_tin_3', 'text', [
                'mapped' => false,
                'data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be number.']),
                    new Length([
                        'min' => 4,
                        'max' => 4,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
                ],
            ])
        ;

        if (true === $this->withMaritalStatus) {
            $builder->add('marital_status', 'choice', [
                    'choices' => Profile::getMaritalStatusChoices(),
                    'placeholder' => 'Choose an Option',
                    'required' => false,
                ])
                //->add('spouse', new ClientSpouseFormType());
                ->add('spouse_first_name', 'text', ['required' => false])
                ->add('spouse_middle_name', 'text', ['required' => false])
                ->add('spouse_last_name', 'text', ['required' => false])
                ->add('spouse_birth_date', 'date', [
                    'widget' => 'single_text',
                    'format' => 'dd-MM-yyyy',
                    'required' => false,
                    'attr' => ['class' => 'jq-date input-small'],
                ]);
        }

        $builder->add('employment_type', 'choice', [
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('income_source', 'choice', [
                'choices' => PersonalInformation::getIncomeSourceChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('employer_name', 'text', ['required' => false])
            ->add('industry', 'text', ['required' => false])
            ->add('occupation', 'text', ['required' => false])
            ->add('business_type', 'text', ['required' => false])
            ->add('employer_address', 'text', ['required' => false])
            ->add('employment_city', 'text', ['required' => false])
            ->add('employmentState', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('employment_zip', 'text', ['required' => false])
            ->add('is_senior_political_figure', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('senior_spf_name', 'text', ['required' => false])
            ->add('senior_political_title', 'text', ['required' => false])
            ->add('senior_account_owner_relationship', 'text', ['required' => false])
            ->add('senior_country_office', 'text', ['required' => false])
            ->add('is_publicly_traded_company', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('publicle_company_name', 'text', ['required' => false])
            ->add('publicle_address', 'text', ['required' => false])
            ->add('publicle_city', 'text', ['required' => false])
            ->add('publicleState', 'entity', [
                'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('is_broker_security_exchange_person', 'choice', [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('broker_security_exchange_company_name', 'text', ['required' => false])
            ->add('compliance_letter_file', 'file', ['required' => false])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validatePreSave']);

        if (!$this->isPreSaved) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validate']);
        }
    }

    public function validatePreSave(FormEvent $event)
    {
        /** @var $data PersonalInformation */
        $data = $event->getData();
        $form = $event->getForm();

        $this->validateSsn($form, $data);
    }

    public function validate(FormEvent $event)
    {
        /** @var $data PersonalInformation */
        $data = $event->getData();
        $form = $event->getForm();

        $this->validateCitizen($form);
        $this->validateMaritalStatus($form, $data);
        $this->validateEmploymentType($form, $data);

        $this->validateSeniorPoliticalFigure($form, $data);
        $this->validatePubliclyTradedCompany($form, $data);
        $this->validateBrokerSecurityExchange($form, $data);
    }

    protected function validateCitizen(FormInterface $form)
    {
        if ($form->has('citezen')) {
            $citizen = $form->get('citezen')->getData();

            if (!$citizen || $citizen !== 'us') {
                $form->get('citezen')->addError(new FormError('You must be US citizen or resident.'));
            }
        }
    }

    protected function validateSsn(FormInterface $form, $data)
    {
        if ($form->has('ssn_tin_1') && $form->has('ssn_tin_2') && $form->has('ssn_tin_3')) {
            $ssnTin1 = $form->get('ssn_tin_1')->getData();
            $ssnTin2 = $form->get('ssn_tin_2')->getData();
            $ssnTin3 = $form->get('ssn_tin_3')->getData();

            $data->setSsnTin($ssnTin1.$ssnTin2.$ssnTin3);
        }
    }

    protected function validateEmploymentType(FormInterface $form, $data)
    {
        if ($form->has('employment_type')) {
            $employmentType = $form->get('employment_type')->getData();

            if (!in_array($employmentType, array_keys(Profile::getEmploymentTypeChoices()))) {
                $form->get('employment_type')->addError(new FormError('Required.'));
            } else {
                if ($employmentType === Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED ||
                    $employmentType === Profile::CLIENT_EMPLOYMENT_TYPE_UNEMPLOYED
                ) {
                    $data->setEmployerName(null);
                    $data->setIndustry(null);
                    $data->setOccupation(null);
                    $data->setBusinessType(null);
                    $data->setEmployerAddress(null);
                    $data->setEmploymentCity(null);
                    $data->setEmploymentState(null);

                   /* if ($form->has('zipcode')) {
                        $data->setEmploymentZip(null);
                    }*/

                    if (!in_array($data->getIncomeSource(), PersonalInformation::getIncomeSourceChoices())) {
                        $form->get('income_source')->addError(new FormError('Required.'));
                    }
                } else {
                    $data->setIncomeSource(null);

                    $employerName = $data->getEmployerName();
                    $industry = $data->getIndustry();
                    $occupation = $data->getOccupation();
                    $businessType = $data->getBusinessType();
                    $employerAddress = $data->getEmployerAddress();
                    $city = $data->getEmploymentCity();
                    $state = $data->getEmploymentState();

                    if (is_null($employerName) || !is_string($employerName)) {
                        $form->get('employer_name')->addError(new FormError('Required.'));
                    }
                    if (is_null($industry) || !is_string($industry)) {
                        $form->get('industry')->addError(new FormError('Required.'));
                    }
                    if (is_null($occupation) || !is_string($occupation)) {
                        $form->get('occupation')->addError(new FormError('Required.'));
                    }
                    if (is_null($businessType) || !is_string($businessType)) {
                        $form->get('business_type')->addError(new FormError('Required.'));
                    }
                    if (is_null($employerAddress) || !is_string($employerAddress)) {
                        $form->get('employer_address')->addError(new FormError('Required.'));
                    }
                    if (is_null($city) || !is_string($city)) {
                        $form->get('employment_city')->addError(new FormError('Required.'));
                    }
                    if ($form->has('employment_state')) {
                        if (null === $state) {
                            $form->get('employment_state')->addError(new FormError('Required.'));
                        }
                    }

                    if ($form->has('employment_zip')) {
                        $zipDigits = 5;
                        $zip = str_replace([' ', '-'], '', $data->getEmploymentZip());

                        if (!is_numeric($zip)) {
                            $form->get('employment_zip')->addError(new FormError('Enter correct zip code.'));
                        } elseif (strlen($zip) !== $zipDigits) {
                            $form->get('employment_zip')->addError(new FormError("Zip code must be {$zipDigits} digits."));
                        } else {
                            $data->setEmploymentZip($zip);
                        }
                    }
                }
            }
        }
    }

    protected function validateMaritalStatus(FormInterface $form)
    {
        if ($form->has('marital_status')) {
            $maritalStatus = $form->get('marital_status')->getData();

            if (!in_array($maritalStatus, Profile::getMaritalStatusChoices())) {
                $form->get('marital_status')->addError(new FormError('Required.'));
            }

            if ($maritalStatus === Profile::CLIENT_MARITAL_STATUS_MARRIED) {
                if ($form->has('spouse_first_name')) {
                    $firstName = $form->get('spouse_first_name')->getData();

                    if (is_null($firstName) || !is_string($firstName)) {
                        $form->get('spouse_first_name')->addError(new FormError('Enter first name.'));
                    }
                }

                if ($form->has('spouse_last_name')) {
                    $firstName = $form->get('spouse_last_name')->getData();

                    if (is_null($firstName) || !is_string($firstName)) {
                        $form->get('spouse_last_name')->addError(new FormError('Enter last name.'));
                    }
                }

                if ($form->has('spouse_last_name')) {
                    $minYears = 18;
                    $birthDate = $form->get('spouse_birth_date')->getData();

                    if (!$birthDate) {
                        $form->get('spouse_birth_date')->addError(new FormError('Enter spouse date of birth.'));
                    } else {
                        $nowDate = new \DateTime('now');
                        $interval = $nowDate->diff($birthDate);

                        if ((int) $interval->format('%y%') < $minYears) {
                            $form->get('spouse_birth_date')->addError(new FormError("Your spouse must be at least {$minYears} years old."));
                        }
                    }
                }
            }
        }
    }

    protected function validateSeniorPoliticalFigure(FormInterface $form, $data)
    {
        $isSeniorPolitical = $data->getIsSeniorPoliticalFigure();

        if (is_null($isSeniorPolitical)) {
            $form->get('is_senior_political_figure')->addError(new FormError('Required.'));
        } elseif ($isSeniorPolitical) {
            $relationship = $data->getSeniorAccountOwnerRelationship();
            $countryOffice = $data->getSeniorCountryOffice();
            $title = $data->getSeniorPoliticalTitle();
            $spfName = $data->getSeniorSpfName();

            if (is_null($relationship) || !is_string($relationship)) {
                $form->get('senior_account_owner_relationship')->addError(new FormError('Required.'));
            }
            if (is_null($countryOffice) || !is_string($countryOffice)) {
                $form->get('senior_country_office')->addError(new FormError('Required.'));
            }
            if (is_null($title) || !is_string($title)) {
                $form->get('senior_political_title')->addError(new FormError('Required.'));
            }
            if (is_null($spfName) || !is_string($spfName)) {
                $form->get('senior_spf_name')->addError(new FormError('Required.'));
            }
        } else {
            $data->setSeniorAccountOwnerRelationship(null);
            $data->setSeniorCountryOffice(null);
            $data->setSeniorPoliticalTitle(null);
            $data->setSeniorSpfName(null);
        }
    }

    protected function validatePubliclyTradedCompany(FormInterface $form, $data)
    {
        $isPubliclyTraded = $data->getIsPubliclyTradedCompany();

        if (is_null($isPubliclyTraded)) {
            $form->get('is_publicly_traded_company')->addError(new FormError('Required.'));
        } elseif ($isPubliclyTraded) {
            $name = $data->getPublicleCompanyName();
            $address = $data->getPublicleAddress();
            $city = $data->getPublicleCity();
            $state = $data->getPublicleState();

            if (is_null($name) || !is_string($name)) {
                $form->get('publicle_company_name')->addError(new FormError('Required.'));
            }
            if (is_null($address) || !is_string($address)) {
                $form->get('publicle_address')->addError(new FormError('Required.'));
            }
            if (is_null($city) || !is_string($city)) {
                $form->get('publicle_city')->addError(new FormError('Required.'));
            }
            if (null === $state) {
                $form->get('publicleState')->addError(new FormError('Required.'));
            }
        } else {
            $data->setPublicleAddress(null);
            $data->setPublicleCity(null);
            $data->setPublicleCompanyName(null);
            $data->setPublicleState(null);
        }
    }

    protected function validateBrokerSecurityExchange(FormInterface $form, $data)
    {
        $isBrokerSecurityExchange = $data->getIsBrokerSecurityExchangePerson();

        if (is_null($isBrokerSecurityExchange)) {
            $form->get('is_broker_security_exchange_person')->addError(new FormError('Required.'));
        } elseif ($isBrokerSecurityExchange) {
            $complianceLetterFile = $data->getComplianceLetterFile();
            $companyName = $data->getBrokerSecurityExchangeCompanyName();

            if (!($complianceLetterFile instanceof UploadedFile)) {
                if ($form->has('compliance_letter_file')) {
                    $form->get('compliance_letter_file')->addError(new FormError('Required.'));
                }
            }
            if (is_null($companyName) || !is_string($companyName)) {
                $form->get('broker_security_exchange_company_name')->addError(new FormError('Required.'));
            }
        } else {
            $data->setBrokerSecurityExchangeComplianceLetter(null);
            $data->setBrokerSecurityExchangeCompanyName(null);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'personal_information';
    }
}
