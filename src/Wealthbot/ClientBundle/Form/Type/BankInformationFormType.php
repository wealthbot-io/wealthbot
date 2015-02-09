<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Wealthbot\ClientBundle\Entity\BankInformation;
use Wealthbot\ClientBundle\Form\Validator\BankInformationFormValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Type;

class BankInformationFormType extends AbstractType
{
    private $isPreSaved;

    public function __construct($isPreSaved = false)
    {
        $this->isPreSaved = $isPreSaved;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account_owner_first_name', 'text', array('required' => false))
            ->add('account_owner_middle_name', 'text', array('required' => false))
            ->add('account_owner_last_name', 'text', array('required' => false))
            ->add('joint_account_owner_first_name', 'text', array('required' => false))
            ->add('joint_account_owner_middle_name', 'text', array('required' => false))
            ->add('joint_account_owner_last_name', 'text', array('required' => false))
            ->add('name', 'text', array('required' => false))
            ->add('account_title', 'text', array('required' => false))
            ->add('phone_number', 'text', array('required' => false))
            ->add('routing_number', 'text', array(
                'constraints' => array(
                    new Type(array('type' => 'numeric'))
                ),
                'required' => false
            ))
            ->add('account_number', 'text', array(
                'constraints' => array(
                    new Type(array('type' => 'numeric'))
                ),
                'required' => false
            ))
            ->add('account_type', 'choice', array(
                'choices' => BankInformation::getAccountTypeChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => false
            ))
            ->add('pdfDocument', new PdfDocumentFormType());
        ;

        $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
    }

    public function onBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $cleanedPhoneNumber = str_replace(array(' ', '-', '(', ')'), '', $data->getPhoneNumber());
        $data->setPhoneNumber($cleanedPhoneNumber);

        $bankInformationValidator = new BankInformationFormValidator($form, $data);
        $bankInformationValidator->validate();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\BankInformation'
        ));
    }

    public function getName()
    {
        return 'bank_information';
    }
}