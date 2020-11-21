<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use App\Entity\BankInformation;
use App\Form\Validator\BankInformationFormValidator;

class BankInformationFormType extends AbstractType
{
    private $isPreSaved;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isPreSaved = $options['is_pre_saved'];

        $builder
            ->add('account_owner_first_name', TextType::class, ['required' => false])
            ->add('account_owner_middle_name', TextType::class, ['required' => false])
            ->add('account_owner_last_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_first_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_middle_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_last_name', TextType::class, ['required' => false])
            ->add('name', TextType::class, ['required' => false])
            ->add('account_title', TextType::class, ['required' => false])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('routing_number', TextType::class, [
                'constraints' => [
                    new Type(['type' => 'numeric']),
                ],
                'required' => false,
            ])
            ->add('account_number', TextType::class, [
                'constraints' => [
                    new Type(['type' => 'numeric']),
                ],
                'required' => false,
            ])
            ->add('account_type', ChoiceType::class, [
                'choices' => BankInformation::getAccountTypeChoices(),
            ])
            ->add('pdfDocument', PdfDocumentFormType::class);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $cleanedPhoneNumber = str_replace([' ', '-', '(', ')'], '', $data->getPhoneNumber());
        $data->setPhoneNumber($cleanedPhoneNumber);

        $bankInformationValidator = new BankInformationFormValidator($form, $data);
        $bankInformationValidator->validate();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\BankInformation',
            'is_pre_saved' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'bank_information';
    }
}
