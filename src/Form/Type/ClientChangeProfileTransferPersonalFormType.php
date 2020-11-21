<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Form\Validator\ClientSpouseFormValidator;
use App\Model\AccountOwnerInterface;
use App\Model\UserAccountOwnerAdapter;
use App\Entity\Profile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientChangeProfileTransferPersonalFormType extends AccountOwnerPersonalInformationFormType
{
    protected $em;
    protected $owner;
    protected $primaryAccount;
    protected $class;
    protected $isPreSaved;
    protected $withMaritalStatus;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->class = $options['class'];
        $this->isPreSaved = $options['isPreSaved'];
        $this->withMaritalStatus = $options['withMaterialStatus'];
        $this->em = $options['em'];
        $this->owner = $options['owner'];
        $this->primaryAccount = $options['owner'];


        parent::buildForm($builder, $options);

        $data = $builder->getData();

        $isExist = $data->getId();

        $builder
            ->remove('citezen')
            ->remove('ssn_tin_1')
            ->remove('ssn_tin_2')
            ->remove('ssn_tin_3')
            ->remove('is_senior_political_figure')
            ->remove('senior_spf_name')
            ->remove('senior_political_title')
            ->remove('senior_account_owner_relationship')
            ->remove('senior_country_office')
            ->remove('is_publicly_traded_company')
            ->remove('publicle_company_name')
            ->remove('publicle_address')
            ->remove('publicle_city')
            ->remove('publicleState')
            ->remove('is_broker_security_exchange_person')
            ->remove('broker_security_exchange_company_name')
            ->remove('compliance_letter_file')
            ->add('email', EmailType::class)
            ->add('first_name', TextType::class, [
                'required' => false,
                'disabled' => true,
            ])
            ->add('middle_name', TextType::class, [
                'required' => false,
                'disabled' => true,
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
                'disabled' => true,
            ])
            ->add('birth_date', DateType::class, [
                    'widget' => 'single_text',
                    'format' => 'MM-dd-yyyy',
                    'required' => true,
                    'disabled' => true,
                    'attr' => ['class' => 'jq-date input-small'],
                ])
            ->add('citizenship', ChoiceType::class, [
                'choices' => [
                    'Yes' => 1,
                    'No' => 0,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'mapped' => false,
                'data' => $isExist ? 1 : null,
                'label' => ($data && 'Married' === $data->getMaritalStatus() ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
                'disabled' => true,
            ])
            ->add('marital_status', ChoiceType::class, [
                'choices' => Profile::getMaritalStatusChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('spouse', ClientSpouseFormType::class)
            ->add('annual_income', ChoiceType::class, [
                'choices' => Profile::getAnnualIncomeChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('estimated_income_tax', PercentType::class, [
                'scale' => 0,
                'required' => false,
                'label' => 'What is your estimated income tax bracket?',
            ])
            ->add('liquid_net_worth', ChoiceType::class, [
                'choices' => Profile::getLiquidNetWorthChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
        ;

        $formFactory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formFactory) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                return;
            }

            if (isset($data['marital_status'])) {
                $form->remove('citizenship');
                $form->add(
                    $formFactory->createNamed(
                        'citizenship',
                        ChoiceType::class,
                        null,
                        [
                        'choices' => [
                            'Yes' => 1,
                            'No' => 0,
                        ],
                        'expanded' => true,
                        'multiple' => false,
                        'required' => false,
                        'mapped' => false,
                        'data' => (isset($data['id']) ? 1 : null),
                        'label' => ('Married' === $data['marital_status'] ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
                        'disabled' => true,
                        'auto_initialize' => false,
                    ]
                )
                );
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'changeProfileValidate']);
    }

    public function changeProfileValidate(FormEvent $event)
    {
        /** @var UserAccountOwnerAdapter $data */
        $data = $event->getData();
        $form = $event->getForm();

        if ($form->has('spouse') && Profile::CLIENT_MARITAL_STATUS_MARRIED === $data->getMaritalStatus()) {
            $spouseValidator = new ClientSpouseFormValidator($form->get('spouse'), $data->getSpouse());
            $spouseValidator->validate();
        }

        $phoneDigits = 10;
        $phoneNum = str_replace([' ', '-', '(', ')'], '', $data->getPhoneNumber());

        if ($form->has('phone_number') && !is_numeric($phoneNum)) {
            $form->get('phone_number')->addError(new FormError('Enter correct phone number.'));
        } elseif ($form->has('phone_number') && strlen($phoneNum) !== $phoneDigits) {
            $form->get('phone_number')->addError(new FormError("Phone number must be {$phoneDigits} digits."));
        }

        if ($form->has('email')) {
            if (!filter_var($data->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $form->get('email')->addError(new FormError('Invalid email address.'));
            }

            $exist = $this->em->getRepository('App\Entity\User')->findOneBy(['email' => $data->getEmail()]);
            if ($exist && $exist->getId() !== $data->getId()) {
                $form->get('email')->addError(new FormError('Email address already exist.'));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'em' => null,
          'owner' => null,
          'isPreSaved' => null,
          'withMaterialStatus' => null,
          'primaryAccount' => null,
           'class' => null
       ]);
    }
}
