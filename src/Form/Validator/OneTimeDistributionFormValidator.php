<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.04.13
 * Time: 17:49
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Validator;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use App\Entity\Distribution;

class OneTimeDistributionFormValidator extends ScheduledDistributionFormValidator
{
    public function __construct(FormInterface $form, Distribution $data)
    {
        $this->form = $form;
        $this->data = $data;
        parent::__construct($form, $data);
    }

    /**
     * Validate form fields.
     */
    public function validate()
    {
        $this->validateTransferMethod();

        if (Distribution::TRANSFER_METHOD_NOT_FUNDING !== $this->form->get('transfer_method')->getData()) {
            $this->validateDistributionMethod();
            $this->validateFederalWithholding();
            $this->validateStateWithholding();

            parent::validate();
        }
    }

    /**
     * Validate type field.
     */
    private function validateTransferMethod()
    {
        if ($this->form->has('transfer_method') && !in_array($this->data->getTransferMethod(), Distribution::getTransferMethodChoices())) {
            $this->form->get('transfer_method')->addError(new FormError('Choose an option.'));
        }
    }

    /**
     * Validate distribution_method field.
     */
    private function validateDistributionMethod()
    {
        if ($this->form->has('distribution_method')) {
            if (!in_array(
                $this->data->getDistributionMethod(),
                array_keys(Distribution::getDistributionMethodChoices())
            )) {
                $this->form->get('distribution_method')->addError(new FormError('Required.'));
            }
        }
    }

    /**
     * Validate federal_withholding and child fields.
     */
    private function validateFederalWithholding()
    {
        if ($this->form->has('federal_withholding')) {
            $federalWithholding = $this->data->getFederalWithholding();

            if (!in_array($federalWithholding, array_keys(Distribution::getFederalWithholdingChoices()))) {
                $this->form->get('federal_withholding')->addError(new FormError('Choose an option.'));
            } else {
                if (Distribution::FEDERAL_WITHHOLDING_TAXES === $federalWithholding) {
                    $percentRate = $this->data->getFederalWithholdPercent();
                    $moneyRate = $this->data->getFederalWithholdMoney();

                    if (!is_numeric($percentRate) && !is_numeric($moneyRate)) {
                        $this->form->get('federal_withholding')->addError(new FormError('Please enter withhold taxes rate.'));
                    } elseif (is_numeric($percentRate) && is_numeric($moneyRate)) {
                        $this->form->get('federal_withholding')->addError(new FormError('Please enter percent or money withhold taxes rate.'));
                    }
                } elseif (Distribution::FEDERAL_WITHHOLDING_TAXES !== $federalWithholding) {
                    $this->data->setFederalWithholdPercent(null);
                    $this->data->setFederalWithholdMoney(null);
                }
            }
        }
    }

    /**
     * Validate state_withholding and child fields.
     */
    private function validateStateWithholding()
    {
        if ($this->form->has('state_withholding')) {
            $stateWithholding = $this->data->getStateWithholding();

            if (!in_array($stateWithholding, array_keys(Distribution::getStateWithholdingChoices()))) {
                $this->form->get('state_withholding')->addError(new FormError('Choose an option.'));
            } elseif (Distribution::STATE_WITHHOLDING_TAXES === $stateWithholding) {
                $percentRate = $this->data->getStateWithholdPercent();
                $moneyRate = $this->data->getStateWithholdMoney();

                if (!is_numeric($percentRate) && !is_numeric($moneyRate)) {
                    $this->form->get('state_withholding')->addError(new FormError('Please enter withhold taxes rate.'));
                } elseif (is_numeric($percentRate) && is_numeric($moneyRate)) {
                    $this->form->get('state_withholding')->addError(new FormError('Please enter percent or money withhold taxes rate.'));
                }

                $this->data->setResidenceState(null);
            } elseif (Distribution::STATE_WITHHOLDING_RESIDENCE_STATE === $stateWithholding && !$this->data->getResidenceState()) {
                $this->form->get('residenceState')->addError(new FormError('Choose an option.'));

                $this->data->setStateWithholdPercent(null);
                $this->data->setStateWithholdMoney(null);
            } else {
                $this->data->setStateWithholdPercent(null);
                $this->data->setStateWithholdMoney(null);
                $this->data->setResidenceState(null);
            }
        }
    }
}
