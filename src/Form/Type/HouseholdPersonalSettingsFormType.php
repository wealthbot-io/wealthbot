<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:57.
 */

namespace App\Form\Type;

use App\Entity\PersonalInformation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Entity\Profile;

class HouseholdPersonalSettingsFormType extends AbstractType
{
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'First Name',
                'property_path' => 'profile.firstName',
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Last Name',
                'property_path' => 'profile.lastName',
            ])
            ->add('middleName', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Middle Name',
                'property_path' => 'profile.middleName',
            ])
            ->add('birthDate', DateType::class, [
                'attr' => ['class' => 'jq-ce-date input-small'],
                'format' => 'MM-dd-yyyy',
                'label' => 'Birth Date',
                'property_path' => 'profile.birthDate',
                'widget' => 'single_text',
            ])
            ->add('citizenship', ChoiceType::class, [
                'expanded' => true,
                'label' => 'U.S. citizen?',
                'data' => 1,
                'choices' => [ 'Yes' => 1, 'No' => 1],
                'mapped' => false,
                'constraints' => [
                    new IsTrue(['message' => 'Your client should be U. S. citizen.']),
            ], ])
        ;

        $builder
            ->add('employmentStatus', ChoiceType::class, [
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'label' => 'Employment Status',
                'multiple' => false,
                'property_path' => 'profile.employmentType',
                'required' => true,
            ])
            ->add('employerName', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'label' => 'Employer Name',
                'property_path' => 'clientPersonalInformation.employerName',
                'required' => false,
            ])
            ->add('industry', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'property_path' => 'clientPersonalInformation.industry',
                'required' => false,
            ])
            ->add('occupation', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'property_path' => 'clientPersonalInformation.occupation',
                'required' => false,
            ])
            ->add('businessType', TextType::class, [
                'attr' => ['class' => 'input-small'],
                'label' => 'Type of Business',
                'property_path' => 'clientPersonalInformation.businessType',
                'required' => false,
            ])
            ->add('employerAddress', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Employer Address',
                'property_path' => 'clientPersonalInformation.employerAddress',
                'required' => false,
            ])
            ->add('employmentCity', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Employment City',
                'property_path' => 'clientPersonalInformation.city',
                'required' => false,
            ])
            ->add('employmentState', EntityType::class, [
                'attr' => ['class' => 'input-medium'],
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'property_path' => 'clientPersonalInformation.state',
                'required' => false,
            ])
            ->add('employmentZip', TextType::class, [
                'attr' => ['class' => 'input-mini'],
                'label' => 'Zip Code',
                'property_path' => 'clientPersonalInformation.zipcode',
                'required' => false,
            ])
        ;

        $builder
            ->add('maritalStatus', ChoiceType::class, [
                'attr' => ['class' => 'input-small'],
                'choices' => Profile::getMaritalStatusChoices(),
                'placeholder' => 'Choose an Option',
                'label' => 'Marital Status',
                'property_path' => 'profile.maritalStatus',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $user = $event->getData();

        $ssnParts = [1 => null,2=> null,3=> null];
        /** @var PersonalInformation $personalInformation */
        $personalInformation = $user->getClientPersonalInformation();
        if ($personalInformation) {
            $ssn = $personalInformation->getSsnTin();
            if (preg_match("~(\d{3})(\d{2})(\d{4})~", $ssn, $matches)) {
                $ssnParts = $matches;
            }
        }

        $form
            ->add($this->factory->createNamed('ssn1', NumberType::class, $ssnParts[1], [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ],
                'auto_initialize' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 3,
                        'max' => 3,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))

            ->add($this->factory->createNamed('ssn2', NumberType::class, $ssnParts[2], [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '##',
                ],
                'auto_initialize' => false,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 2,
                        'max' => 2,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))

            ->add($this->factory->createNamed('ssn3', NumberType::class, $ssnParts[3], [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '####',
                ],
                'mapped' => false,
                'auto_initialize' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Can not be blank.']),
                    new Regex(['pattern' => '/^\d+$/', 'message' => 'Must be a number.']),
                    new Length([
                        'min' => 4,
                        'max' => 4,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.',
                    ]),
            ], ]))
        ;
    }

    public function onSubmitData(FormEvent $event)
    {
        $user = $event->getData();
        $form = $event->getForm();

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $user->getClientPersonalInformation()->setSsnTin($ssn1.$ssn2.$ssn3);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_personal_settings';
    }
}
