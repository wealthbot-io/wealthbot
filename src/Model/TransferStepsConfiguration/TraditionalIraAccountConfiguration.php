<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.13
 * Time: 13:09
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\TransferStepsConfiguration;

use App\Model\AccountGroup;
use App\Model\ClientAccount;
use App\Model\TransferStepsConfigurationInterface;
use App\Manager\AccountDocusignManager;

class TraditionalIraAccountConfiguration implements TransferStepsConfigurationInterface
{
    /**
     * @var AccountDocusignManager
     */
    private $adm;

    /**
     * @var ClientAccount
     */
    private $account;

    public function __construct(AccountDocusignManager $adm, ClientAccount $account)
    {
        $this->adm = $adm;
        $this->account = $account;
    }

    /**
     * Get next transfer screen step by current step.
     *
     * @param string $currentStep
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getNextStep($currentStep)
    {
        switch ($currentStep) {
            case ClientAccount::STEP_ACTION_BASIC:
                $nextStep = ClientAccount::STEP_ACTION_PERSONAL;
                break;

            case ClientAccount::STEP_ACTION_PERSONAL:
                $nextStep = ClientAccount::STEP_ACTION_BENEFICIARIES;
                break;

            case ClientAccount::STEP_ACTION_BENEFICIARIES:
                if ($this->account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
                    $nextStep = ClientAccount::STEP_ACTION_TRANSFER;
                } elseif ($this->account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)) {
                    $nextStep = ClientAccount::STEP_ACTION_ROLLOVER;
                } elseif ($this->hasFundingSection()) {
                    $nextStep = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
                } else {
                    $nextStep = ClientAccount::STEP_ACTION_REVIEW;
                }

                break;

            case ClientAccount::STEP_ACTION_TRANSFER:
                if ($this->account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)) {
                    $nextStep = ClientAccount::STEP_ACTION_ROLLOVER;
                } elseif ($this->hasFundingSection()) {
                    $nextStep = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
                } else {
                    $nextStep = ClientAccount::STEP_ACTION_REVIEW;
                }

                break;

            case ClientAccount::STEP_ACTION_ROLLOVER:
                if ($this->hasFundingSection()) {
                    $nextStep = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
                } else {
                    $nextStep = ClientAccount::STEP_ACTION_REVIEW;
                }

                break;

            case ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING:
                $nextStep = ClientAccount::STEP_ACTION_REVIEW;
                break;

            case ClientAccount::STEP_ACTION_REVIEW:
                $nextStep = ClientAccount::STEP_ACTION_FINISHED;
                break;

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Invalid value of step: "%s" for account with system_type: "%s"',
                    $this->account->getSystemTypeAsString(),
                    $currentStep
                ));

                break;
        }

        return $nextStep;
    }

    /**
     * Get previous  transfer screen step by current step.
     *
     * @param string $currentStep
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getPreviousStep($currentStep)
    {
        switch ($currentStep) {
            case ClientAccount::STEP_ACTION_BASIC:
                $prevStep = '';
                break;

            case ClientAccount::STEP_ACTION_PERSONAL:
                $prevStep = ClientAccount::STEP_ACTION_BASIC;
                break;

            case ClientAccount::STEP_ACTION_BENEFICIARIES:
                $prevStep = ClientAccount::STEP_ACTION_PERSONAL;
                break;

            case ClientAccount::STEP_ACTION_TRANSFER:
                $prevStep = ClientAccount::STEP_ACTION_BENEFICIARIES;
                break;

            case ClientAccount::STEP_ACTION_ROLLOVER:
                if ($this->account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
                    $prevStep = ClientAccount::STEP_ACTION_TRANSFER;
                } else {
                    $prevStep = ClientAccount::STEP_ACTION_BENEFICIARIES;
                }

                break;

            case ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING:
                if ($this->account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)) {
                    $prevStep = ClientAccount::STEP_ACTION_ROLLOVER;
                } elseif ($this->account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
                    $prevStep = ClientAccount::STEP_ACTION_TRANSFER;
                } else {
                    $prevStep = ClientAccount::STEP_ACTION_BENEFICIARIES;
                }

                break;

            case ClientAccount::STEP_ACTION_REVIEW:
                if ($this->hasFundingSection()) {
                    $prevStep = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
                } elseif ($this->account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)) {
                    $prevStep = ClientAccount::STEP_ACTION_ROLLOVER;
                } elseif ($this->account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
                    $prevStep = ClientAccount::STEP_ACTION_TRANSFER;
                } else {
                    $prevStep = ClientAccount::STEP_ACTION_BENEFICIARIES;
                }

                break;

            case ClientAccount::STEP_ACTION_FINISHED:
                $prevStep = ClientAccount::STEP_ACTION_REVIEW;
                break;

            default:
                throw new \InvalidArgumentException(sprintf(
                    'Invalid value of step: "%s" for account with system_type: "%s"',
                    $this->account->getSystemTypeAsString(),
                    $currentStep
                ));

                break;
        }

        return $prevStep;
    }

    /**
     * Is account has funding section.
     *
     * @return bool
     */
    private function hasFundingSection()
    {
        if ($this->account->hasFunding() ||
            $this->account->hasDistributing() ||
            $this->account->hasGroup(AccountGroup::GROUP_DEPOSIT_MONEY) ||
            $this->adm->hasElectronicallySignError($this->account)
        ) {
            return true;
        }

        return false;
    }
}
