<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 26.03.14
 * Time: 23:21
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
use Wealthbot\ClientBundle\Entity\ClientAdditionalContact;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Entity\Profile;

class HouseholdSpouseFormType extends AbstractType
{
    protected $factory, $client;

    public function __construct(User $client)
    {
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $builder
            ->add('firstName', 'text', array(
                'attr' => array(
                    'class' => 'input-medium',
                    'placeholder' => 'First Name'
                ),
                'required' => false
            ))
            ->add('middleName', 'text', array(
                'attr' => array(
                    'class' => 'input-small',
                    'placeholder' => 'Middle Name'
                ),
                'required' => false
            ))
            ->add('lastName', 'text', array(
                'attr' => array(
                    'class' => 'input-medium',
                    'placeholder' => 'Last Name'
                ),
                'required' => false
            ))
            ->add('birthDate', 'date', array(
                'attr' => array(
                    'class' => 'jq-ce-date input-small',
                    'placeholder' => 'MM-DD-YYYY'
                ),
                'format' => 'MM-dd-yyyy',
                'label' => 'Birth Date',
                'required' => false,
                'widget' => 'single_text'
            ))
            ->add('citizenship', 'choice', array(
                'choices' => array('1' => 'Yes', '0' => 'No'),
                'data' => 1,
                'expanded' => true,
                'label' => 'U.S. citizen?',
                'property_path' => false,
                'constraints' => array(
                    new True(array('message' => 'Spouse of your client should be U.S. citizen.'))
            )))
        ;

        $builder
            ->add('employmentType', 'choice', array(
                'choices' => Profile::getEmploymentTypeChoices(),
                'expanded' => true,
                'label' => 'Employment Status',
                'multiple' => false,
                'required' => true
            ))
            ->add('employerName', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Employer Name',
                'required' => false
            ))
            ->add('industry', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'required' => false
            ))
            ->add('occupation', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'required' => false
            ))
            ->add('businessType', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Type of Business',
                'required' => false
            ))
            ->add('employerAddress', 'text', array(
                'attr' => array('class' => 'input-large'),
                'label' => 'Employer Address',
                'required' => false
            ))
            ->add('employmentCity', 'text', array(
                'attr' => array('class' => 'input-medium'),
                'label' => 'Employment City',
                'required' => false
            ))
            ->add('employmentState', 'entity', array(
                'attr' => array('class' => 'input-medium'),
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State',
                'required' => false
            ))
            ->add('employmentZip', 'text', array(
                'attr' => array('class' => 'input-mini'),
                'label' => 'Zip Code',
                'required' => false
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBindData'));
    }

    public function onPresetData(FormEvent $event)
    {
        $form = $event->getForm();
        $spouse = $event->getData();
/*        var_dump($form->get('firstName'));
        exit;

        if (null === $form->get('employmentType')->getData()) {
            $form->get('employmentType')->setData(Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED);
        }*/

        $ssnParts = array(1 => null, null, null);
        if ($spouse) {
            $ssn = $spouse->getSsnTin();
            if (preg_match("~^(\d{3})(\d{2})(\d{4})$~", $ssn, $matches)) {
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
            ))));
    }

    public function onBindData(FormEvent $event)
    {
        $form = $event->getForm();
        $spouse = $event->getData();
        $spouse->setClient($this->client);
        $spouse->setType(ClientAdditionalContact::TYPE_SPOUSE);

        if ($form->has('ssn1') && $form->has('ssn2') && $form->has('ssn3')) {
            $ssn1 = $form->get('ssn1')->getData();
            $ssn2 = $form->get('ssn2')->getData();
            $ssn3 = $form->get('ssn3')->getData();
            $spouse->setSsnTin($ssn1 . $ssn2 . $ssn3);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAdditionalContact'
        ));
    }

    public function getName()
    {
        return 'household_spouse_form';
    }
}
