<?php

namespace Wealthbot\ClientBundle\Form\Type;

use Wealthbot\ClientBundle\Entity\Beneficiary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Min;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class BeneficiaryFormType extends AbstractType
{
    private $isPreSaved;
    private $showSsn;

    public function __construct($isPreSaved = false, $showSsn = false)
    {
        $this->isPreSaved = $isPreSaved;
        $this->showSsn = $showSsn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => Beneficiary::getTypeChoices()
            ))
            ->add('first_name', 'text', array('required' => true))
            ->add('middle_name', 'text', array('required' => false))
            ->add('last_name', 'text', array('required' => true))
            ->add('birth_date', 'date', array(
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',
                'attr' => array('class' => 'jq-date input-small')
            ))
            ->add('city', 'text', array('required' => true))
            ->add('street', 'text', array('required' => true))
            ->add('state', 'entity', array(
                'class' => 'WealthbotAdminBundle:State',
                'label' => 'State',
                'empty_value' => 'Select a State'
            ))
            ->add('zip', 'text', array('required' => true
            ))
            ->add('relationship', 'text', array('required' => true))
            ->add('share', 'number', array('required' => true))
        ;

        $factory = $builder->getFormFactory();
        $showSsn = $this->showSsn;

        $updateSsn = function (FormInterface $form, $ssn) use ($factory) {
            $form->add($factory->createNamed('ssn_1', 'text', null, array(
                    'property_path' => false,
                    'attr' => array('value' => $ssn[1]),
                    'constraints' => array(
                        new NotBlank(array('message' => 'Can not be blank.')),
                        new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be number.')),
                        new Length(array(
                            'min' => 3,
                            'max' => 3,
                            'minMessage' => 'Must have {{ limit }} digits.',
                            'maxMessage' => 'Must have {{ limit }} digits.'
                        )),
                    )
                )))
                ->add($factory->createNamed('ssn_2', 'text', null, array(
                    'property_path' => false,
                    'attr' => array('value' => $ssn[2]),
                    'constraints' => array(
                        new NotBlank(array('message' => 'Can not be blank.')),
                        new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be number.')),
                        new Length(array(
                            'min' => 2,
                            'max' => 2,
                            'minMessage' => 'Must have {{ limit }} digits.',
                            'maxMessage' => 'Must have {{ limit }} digits.'
                        )),
                    )
                )))
                ->add($factory->createNamed('ssn_3', 'text', null, array(
                    'property_path' => false,
                    'attr' => array('value' => $ssn[3]),
                    'constraints' => array(
                        new NotBlank(array('message' => 'Can not be blank.')),
                        new Regex(array('pattern'=>'/^\d+$/','message' => 'Must be number.')),
                        new Length(array(
                            'min' => 4,
                            'max' => 4,
                            'minMessage' => 'Must have {{ limit }} digits.',
                            'maxMessage' => 'Must have {{ limit }} digits.'
                        )),
                    )
                )))
            ;
        };

        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($updateSsn) {
            $form = $event->getForm();
            $data = $event->getData();

            if(array_key_exists('ssn_1', $data) && array_key_exists('ssn_2', $data) && array_key_exists('ssn_3', $data)) {
                $updateSsn($form, array('', $data['ssn_1'], $data['ssn_2'], $data['ssn_3']));
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($updateSsn, $showSsn) {
            $form = $event->getForm();
            $data = $event->getData();

            $ssn = array('', '', '', '');

            if (true === $showSsn) {
                if ($data !== null && preg_match('/^([0-9]{3})([0-9]{2})([0-9]{4})$/', $data->getSsn(), $matches)) {
                    $ssn = $matches;
                }
            }

            $updateSsn($form, $ssn);
        });

        $builder->addEventListener(FormEvents::BIND, array($this, 'validatePreSave'));

        if (!$this->isPreSaved) {
            $builder->addEventListener(FormEvents::BIND, array($this, 'validate'));
        }
    }

    public function validatePreSave(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->has('ssn_1') && $form->has('ssn_2') && $form->has('ssn_3')) {
            $ssn1 = $form->get('ssn_1')->getData();
            $ssn2 = $form->get('ssn_2')->getData();
            $ssn3 = $form->get('ssn_3')->getData();

            $data->setSsn($ssn1 . $ssn2 . $ssn3);
        }

        if ($form->has('zip')) {
            $zip = str_replace(array(' ', '-'), '', $data->getZip());
            $data->setZip($zip);
        }
    }

    public function validate(FormEvent $event)
    {
        /** @var $data Beneficiary */
        $form = $event->getForm();
        $data = $event->getData();

        $type = $data->getType();
        $firstName = $data->getFirstName();
        $middleName = $data->getMiddleName();
        $lastName = $data->getLastName();
        $birthDate = $data->getBirthDate();
        $city = $data->getCity();
        $street = $data->getStreet();
        $state = $data->getState();
        $relationship = $data->getRelationship();
        $share = $data->getShare();

        if (!array_key_exists($type, Beneficiary::getTypeChoices())) {
            $form->get('type')->addError(new FormError('Choose an option.'));
        }
        if (null === $firstName || !is_string($firstName)) {
            $form->get('first_name')->addError(new FormError('Required.'));
        }
        if (!preg_match('/[A-Za-z]/', $middleName)) {
            $form->get('middle_name')->addError(new FormError('Enter least 1 letter.'));
        }
        if (null === $lastName || !is_string($lastName)) {
            $form->get('last_name')->addError(new FormError('Required.'));
        }
        if (!($birthDate instanceof \DateTime)) {
            $form->get('birth_date')->addError(new FormError('Enter correct date.'));
        }
        if (null === $city || !is_string($city)) {
            $form->get('city')->addError(new FormError('Required.'));
        }
        if (null === $street || !is_string($street)) {
            $form->get('street')->addError(new FormError('Required.'));
        }
        if (null === $state) {
            $form->get('state')->addError(new FormError('Required.'));
        }
        if (null === $relationship || !is_string($relationship)) {
            $form->get('relationship')->addError(new FormError('Required.'));
        }
        if (null === $share || !is_numeric($share)) {
            $form->get('relationship')->addError(new FormError('Enter correct value.'));
        }
        if (round($share) < 0.01 || round($share) > 100) {
            $form->get('relationship')->addError(new FormError('Value must be in range between 0.01 and 100'));
        }

        if ($form->has('zip')) {
            $zipDigits = 5;
            $zip = str_replace(array(' ', '-'), '', $data->getZip());

            if (!is_numeric($zip)) {
                $form->get('zip')->addError(new FormError("Enter correct zip code."));
            } elseif (strlen($zip) != $zipDigits) {
                $form->get('zip')->addError(new FormError("Zip code must be {$zipDigits} digits."));
            } else {
                $data->setZip($zip);
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\Beneficiary'
        ));
    }

    public function getName()
    {
        return 'transfer_beneficiary';
    }
}