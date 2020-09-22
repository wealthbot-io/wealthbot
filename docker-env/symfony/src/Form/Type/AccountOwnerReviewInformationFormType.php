<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 11.12.13
 * Time: 13:08.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use App\Entity\Profile;

class AccountOwnerReviewInformationFormType extends AccountOwnerPersonalInformationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', TextType::class, [
                'required' => false,
            ])
            ->add('middle_name', TextType::class, [
                'required' => false,
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
            ])
            ->add('street', TextType::class, ['required' => false])
            ->add('city', TextType::class, ['required' => false])
            ->add('state', EntityType::class, [
                'class' => 'App\\Entity\\State',
                'label' => 'State',
                'placeholder' => 'Select a State',
                'required' => true,
            ])
            ->add('zip', TextType::class, ['required' => false])
            ->add('is_different_address', CheckboxType::class, [
                'label' => 'Is your mailing address different than the one above?',
                'required' => false,
            ])
            ->add('mailing_street', TextType::class, ['required' => false])
            ->add('mailing_city', TextType::class, ['required' => false])
            ->add('mailingState', EntityType::class, [
                'class' => 'App\\Entity\\State',
                'label' => 'Mailing state',
                'placeholder' => 'Select a State',
                'required' => false,
            ])
            ->add('mailing_zip', TextType::class, ['required' => false])
            ->add('birth_date', DateType::class, [
                'widget' => 'single_text',
                'format' => 'MM-dd-yyyy',
                'required' => true,
                'attr' => ['class' => 'jq-date input-small'],
            ])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('email', TextType::class, ['required' => true]);

        parent::buildForm($builder, $options);
    }

    public function validate(FormEvent $event)
    {
        parent::validate($event);

        $this->validateOwnerInformation($event->getForm(), $event->getData());
    }

    private function validateOwnerInformation(FormInterface $form, $data)
    {
        if ($form->has('first_name') && !$data->getFirstName()) {
            $form->get('first_name')->addError(new FormError('Required.'));
        }
        if ($form->has('middle_name') && !preg_match('/[A-Za-z]/', $data->getMiddleName())) {
            $form->get('middle_name')->addError(new FormError('Enter least 1 letter.'));
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
    }
}
