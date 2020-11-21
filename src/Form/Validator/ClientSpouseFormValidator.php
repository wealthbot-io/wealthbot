<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.04.13
 * Time: 16:54
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Validator;

use Symfony\Component\Form\FormError;

class ClientSpouseFormValidator extends AbstractFormValidator
{
    public function validate()
    {
        if (is_null($this->data->getFirstName()) || !is_string($this->data->getFirstName())) {
            $this->form->get('first_name')->addError(new FormError('Enter first name.'));
        }

        if (is_null($this->data->getMiddleName()) || strlen(trim($this->data->getMiddleName())) < 1) {
            $this->form->get('middle_name')->addError(new FormError('Enter least 1 letter.'));
        }

        if (is_null($this->data->getLastName()) || !is_string($this->data->getLastName())) {
            $this->form->get('last_name')->addError(new FormError('Enter last name.'));
        }

        if (!$this->data->getBirthDate() || !($this->data->getBirthDate() instanceof \DateTime)) {
            $this->form->get('birth_date')->addError(new FormError('Enter correct date.'));
        } else {
            $minYears = 18;
            $nowDate = new \DateTime();

            $birthDate = $this->data->getBirthDate();
            $spouseInterval = $nowDate->diff($birthDate);

            if ((int) $spouseInterval->format('%y%') < $minYears) {
                $this->form->get('birth_date')->addError(
                    new FormError("Your spouse must be at least {$minYears} years old.")
                );
            }
        }

        if ($this->form->has('birth_date')) {
            $birthDateData = $this->form->get('birth_date')->getData();

            if ($birthDateData && $birthDateData instanceof \DateTime) {
                $year = (int) $birthDateData->format('Y');

                if ($year < 1900) {
                    $this->form->get('birth_date')->addError(new FormError('year must start with 19 or 20 e.g. 1980'));
                }
            } else {
                $this->form->get('birth_date')->addError(new FormError('date format must be MM-DD-YYYY'));
            }
        }
    }
}
