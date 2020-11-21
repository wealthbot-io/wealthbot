<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 10.04.14
 * Time: 1:04.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\SystemAccount;
use App\Entity\User;

class HouseholdCloseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData();
        $hasUnclosedAccounts = false;

        foreach ($client->getSystemAccounts() as $account) {
            if (SystemAccount::STATUS_CLOSED !== $account->getStatus()) {
                $hasUnclosedAccounts = true;
            }
        }

        $builder
            ->add('enabled', ChoiceType::class, [
                'attr' => ['class' => 'jq-ce-date input-medium'],
                'choices' => [
                    'Household active' => 1,
                    'Household closed' => 0,
                ],
                'disabled' => $hasUnclosedAccounts,
                'label' => 'Status: ',
            ])
            ->add('closed', DateType::class, [
                'attr' => ['class' => 'jq-ce-date input-small'],
                'format' => 'MM-dd-yyyy',
                'required' => false,
                'widget' => 'single_text',
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onSubmitData(FormEvent $event)
    {
        /* @var User $user */
        $user = $event->getData();
        $form = $event->getForm();

        if (!$user->isEnabled() && !$form->get('closed')->getData()) {
            $form->get('closed')->addError(new FormError('This field is required when closing account'));
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
        return 'household_close';
    }
}
