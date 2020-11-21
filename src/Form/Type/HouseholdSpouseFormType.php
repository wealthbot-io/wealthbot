<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 26.03.14
 * Time: 23:21.
 */

namespace App\Form\Type;

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
use App\Entity\ClientAdditionalContact;
use App\Entity\Profile;
use App\Entity\User;

class HouseholdSpouseFormType extends AbstractType
{
    protected $factory;
    protected $client;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->client = $options['client'];
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'input-medium',
                    'placeholder' => 'First Name',
                ],
                'required' => false,
            ])
            ->add('middleName', TextType::class, [
                'attr' => [
                    'class' => 'input-small',
                    'placeholder' => 'Middle Name',
                ],
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'input-medium',
                    'placeholder' => 'Last Name',
                ],
                'required' => false,
            ])
            ->add('birthDate', DateType::class, [
                'attr' => [
                    'class' => 'jq-ce-date input-small',
                    'placeholder' => 'MM-DD-YYYY',
                ],
                'format' => 'MM-dd-yyyy',
                'label' => 'Birth Date',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('citizenship', ChoiceType::class, [
                'choices' => ['Yes'=>'1', 'No'=>'0'],
                'data' => 1,
                'expanded' => true,
                'label' => 'U.S. citizen?',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(['message' => 'Spouse of your client should be U.S. citizen.']),
            ], ])
        ;

        $builder
            ->add('employmentType', ChoiceType::class, [
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'label' => 'Employment Status',
                'multiple' => false,
                'required' => true,
            ])
            ->add('employerName', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Employer Name',
                'required' => false,
            ])
            ->add('industry', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'required' => false,
            ])
            ->add('occupation', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'required' => false,
            ])
            ->add('businessType', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Type of Business',
                'required' => false,
            ])
            ->add('employerAddress', TextType::class, [
                'attr' => ['class' => 'input-large'],
                'label' => 'Employer Address',
                'required' => false,
            ])
            ->add('employmentCity', TextType::class, [
                'attr' => ['class' => 'input-medium'],
                'label' => 'Employment City',
                'required' => false,
            ])
            ->add('employmentState', EntityType::class, [
                'attr' => ['class' => 'input-medium'],
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('employmentZip', TextType::class, [
                'attr' => ['class' => 'input-mini'],
                'label' => 'Zip Code',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPresetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var ClientAdditionalContact $spouse */
        $spouse = $event->getData();
        /* if (null === $form->get('employmentType')->getData()) {
                    $form->get('employmentType')->setData(Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED);
                }*/

        $ssnParts = [1 => null, 2 => null, 3 => null];
        if ($spouse) {
            $ssn = $spouse->getSsnTin();
            if (preg_match("~^(\d{3})(\d{2})(\d{4})$~", $ssn, $matches)) {
                $ssnParts = $matches;
            }
        }

        $form
            ->add($this->factory->createNamed('ssn1', NumberType::class, $ssnParts[1], [
                'attr' => [
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ],
                'mapped' => false,
                'auto_initialize' => false,
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
                'mapped' => false,
                'auto_initialize' => false,
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
            ], ]));
    }

    public function onSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $spouse = $event->getData();
        $spouse->setClient($this->client);
        $spouse->setType(ClientAdditionalContact::TYPE_SPOUSE);

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $spouse->setSsnTin($ssn1.$ssn2.$ssn3);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAdditionalContact',
            'client' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'household_spouse_form';
    }
}
