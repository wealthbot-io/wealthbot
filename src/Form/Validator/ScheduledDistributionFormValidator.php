<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.04.13
 * Time: 17:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Validator;

use Symfony\Component\Form\FormError;
use App\Entity\Distribution;

class ScheduledDistributionFormValidator extends AbstractFormValidator
{
    /**
     * Validate form fields.
     */
    public function validate()
    {
        $this->validateStartTransfer();
        $this->validateAmount();
        $this->validateFrequency();
    }

    /**
     * Validate start_transfer fields.
     */
    private function validateStartTransfer()
    {
        if ($this->form->has('transfer_date_month') && $this->form->has('transfer_date_day')) {
            if (!($this->data->getTransferDate() instanceof \DateTime)) {
                $this->form->get('transfer_date_month')->addError(new FormError('Enter correct date.'));
            } else {
                $minDate = new \DateTime('+5 days');

                if ($this->data->getTransferDate() < $minDate) {
                    $this->form->get('transfer_date_month')->addError(
                        new FormError(
                            'The start of your transfer should be at least 5 days after today’s date.'
                        )
                    );
                }
            }
        }
    }

    /**
     * Validate amount field.
     */
    private function validateAmount()
    {
        if ($this->form->has('amount') && !$this->data->getAmount()) {
            $this->form->get('amount')->addError(new FormError('Required.'));
        }
    }

    /**
     * Validate frequency field.
     */
    private function validateFrequency()
    {
        if ($this->form->has('frequency')) {
            $frequencyChoices = Distribution::getFrequencyChoices();

            if (!in_array($this->data->getFrequency(), array_keys($frequencyChoices))) {
                $this->form->get('frequency')->addError(new FormError('Choose an option.'));
            }
        }
    }
}
