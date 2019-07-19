<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 08.02.13
 * Time: 15:32
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use App\Model\AccountOwnerInterface;

class TransferClientInfoFormType extends TransferBasicFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('is_different_address')
            ->remove('mailing_street')
            ->remove('mailing_city')
            ->remove('mailingState')
            ->remove('mailing_zip')
            ->remove('birth_date')
        ;

        $factory = $builder->getFormFactory();
        $updateSsn = function (FormInterface $form, $ssn) use ($factory) {
            $form->add($factory->createNamed('ssn', TextType::class, null, [
                'required' => false,
                'mapped' => false,
                'attr' => ['value' => $ssn],
                'auto_initialize' => false,
            ]));
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($updateSsn) {
            $form = $event->getForm();
            $data = $event->getData();

            if (array_key_exists('ssn', $data)) {
                $updateSsn($form, $data['ssn']);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($updateSsn) {
            /** @var $data AccountOwnerInterface */
            $data = $event->getData();
            $form = $event->getForm();

            $updateSsn($form, $data->getSsnTin());
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var $data AccountOwnerInterface */
            $data = $event->getData();
            $form = $event->getForm();

            if ($form->has('ssn')) {
                $ssnDigits = 9;
                $ssn = str_replace([' ', '-', '(', ')'], '', $form->get('ssn')->getData());

                if (!is_numeric($ssn)) {
                    $form->get('ssn')->addError(new FormError('Enter correct ssn.'));
                } elseif (strlen($ssn) !== $ssnDigits) {
                    $form->get('ssn')->addError(new FormError("Ssn must be {$ssnDigits} digits."));
                }

                $data->setSsnTin($ssn);
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'client_info';
    }
}
