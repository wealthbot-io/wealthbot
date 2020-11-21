<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Validator;

use Symfony\Component\Form\FormError;
use App\Entity\BankInformation;

class BankInformationFormValidator extends AbstractFormValidator
{
    public function validate()
    {
        $form = $this->form;

        /** @var BankInformation $data */
        $data = $this->data;

        if ($this->isNullOrEmptyString($data->getAccountOwnerFirstName())) {
            $form->get('account_owner_first_name')->addError(new FormError('Required.'));
        }

        if ($this->isNullOrEmptyString($data->getAccountOwnerLastName())) {
            $form->get('account_owner_last_name')->addError(new FormError('Required.'));
        }

        if ($this->isNullOrEmptyString($data->getName())) {
            $form->get('name')->addError(new FormError('Required.'));
        }

        if ($this->isNullOrEmptyString($data->getAccountTitle())) {
            $form->get('account_title')->addError(new FormError('Required.'));
        }

        $phoneNumber = $data->getPhoneNumber();
        if ($this->isNullOrEmptyString($phoneNumber)) {
            if ($form->has('phone_number')) {
                $form->get('phone_number')->addError(new FormError('Required.'));
            }
        } else {
            $phoneDigits = 10;

            if ($form->has('phone_number')) {
                if (!is_numeric($phoneNumber)) {
                    $form->get('phone_number')->addError(new FormError('Enter correct phone number.'));
                } elseif (strlen($phoneNumber) !== $phoneDigits) {
                    $form->get('phone_number')->addError(
                        new FormError("Phone number must be {$phoneDigits} digits.")
                    );
                }
            }
        }

        if (!is_numeric($data->getRoutingNumber())) {
            $form->get('routing_number')->addError(new FormError('Required.'));
        }
        if (!is_numeric($data->getAccountNumber())) {
            $form->get('account_number')->addError(new FormError('Required.'));
        }
        if (!in_array($data->getAccountType(), BankInformation::getAccountTypeChoices())) {
            $form->get('account_type')->addError(new FormError('Required.'));
        }

        if (!$data->getPdfDocument() && $form->has('pdfDocument')) {
            $form->get('pdfDocument')->addError(new FormError('Upload a file.'));
        }
    }

    /**
     * Returns true if string is null or empty.
     *
     * @param $str
     *
     * @return bool
     */
    private function isNullOrEmptyString($str)
    {
        return is_null($str) || '' === trim($str);
    }
}
