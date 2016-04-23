<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:57
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Wealthbot\UserBundle\Entity\Profile;

class HouseholdPersonalSettingsFormType extends AbstractType
{
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('firstName', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'First Name',
                'property_path' => 'profile.firstName'
            ))
            ->add('lastName', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Last Name',
                'property_path' => 'profile.lastName'
            ))
            ->add('middleName', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Middle Name',
                'property_path' => 'profile.middleName'
            ))
            ->add('birthDate', 'date', array(
                'attr' => array('class' => 'jq-ce-date input-small'),
                'format' => 'MM-dd-yyyy',
                'label' => 'Birth Date',
                'property_path' => 'profile.birthDate',
                'widget' => 'single_text'
            ))
            ->add('citizenship', 'choice', array(
                'expanded' => true,
                'label' => 'U.S. citizen?',
                'data' => 1,
                'choices' => array('1' => 'Yes', '0' => 'No'),
                'property_path' => false,
                'constraints' => array(
                    new True(array('message' => 'Your client should be U. S. citizen.'))
            )))
        ;

        $builder
            ->add('employmentStatus', 'choice', array(
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'label' => 'Employment Status',
                'multiple' => false,
                'property_path' => 'profile.employmentType',
                'required' => true
            ))
            ->add('employerName', 'text', array(
                'attr' => array('class' => 'input-small'),
                'label' => 'Employer Name',
                'property_path' => 'clientPersonalInformation.employerName',
                'required' => false
            ))
            ->add('industry', 'text', array(
                'attr' => array('class' => 'input-small'),
                'property_path' => 'clientPersonalInformation.industry',
                'required' => false
            ))
            ->add('occupation', 'text', array(
                'attr' => array('class' => 'input-small'),
                'property_path' => 'clientPersonalInformation.occupation',
                'required' => false
            ))
            ->add('businessType', 'text', array(
                'attr' => array('class' => 'input-small'),
                'label' => 'Type of Business',
                'property_path' => 'clientPersonalInformation.businessType',
                'required' => false
            ))
            ->add('employerAddress', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Employer Address',
                'property_path' => 'clientPersonalInformation.employerAddress',
                'required' => false
            ))
            ->add('employmentCity', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Employment City',
                'property_path' => 'clientPersonalInformation.city',
                'required' => false
            ))
            ->add('employmentState', 'entity', array(
                'attr' => array('class' => 'input-medium'),
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State',
                'property_path' => 'clientPersonalInformation.state',
                'required' => false
            ))
            ->add('employmentZip', 'text', array(
                'attr' => array('class' => 'input-mini'),
                'label' => 'Zip Code',
                'property_path' => 'clientPersonalInformation.zipcode',
                'required' => false
            ))
        ;

        $builder
            ->add('maritalStatus', 'choice', array(
                'attr' => array('class' => 'input-small'),
                'choices' => Profile::getMaritalStatusChoices(),
                'empty_value' => 'Choose an Option',
                'label' => 'Marital Status',
                'property_path' => 'profile.maritalStatus',
                'required' => false
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBindData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $user = $event->getData();

        $ssnParts = array(1 => null, null, null);
        $personalInformation = $user->getClientPersonalInformation();
        if ($personalInformation) {
            $ssn = $personalInformation->getSsnTin();
            if (preg_match("~(\d{3})(\d{2})(\d{4})~", $ssn, $matches)) {
                $ssnParts = $matches;
            }
        }

        $form
            ->add($this->factory->createNamed('ssn1', 'number', $ssnParts[1], array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '###',
                ),
                'property_path' => false,
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 3,
                        'max' => 3,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))

            ->add($this->factory->createNamed('ssn2', 'number', $ssnParts[2], array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '##',
                ),
                'property_path' => false,
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 2,
                        'max' => 2,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))

            ->add($this->factory->createNamed('ssn3', 'number', $ssnParts[3], array(
                'attr' => array(
                    'class' => 'input-xmini',
                    'placeholder' => '####',
                ),
                'property_path' => false,
                'constraints' => array(
                    new NotBlank(array('message' => 'Can not be blank.')),
                    new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be a number.')),
                    new Length(array(
                        'min' => 4,
                        'max' => 4,
                        'minMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'maxMessage' => 'SSN should be in the format: ### - ## - ####.',
                        'exactMessage' => 'SSN should be in the format: ### - ## - ####.'
                    ))
            ))))
        ;
    }

    public function onBindData(FormEvent $event)
    {
        $user = $event->getData();
        $form = $event->getForm();

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $user->getClientPersonalInformation()->setSsnTin($ssn1 . $ssn2 . $ssn3);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'client_personal_settings';
    }
}
