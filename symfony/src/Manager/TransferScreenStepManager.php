<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.13
 * Time: 12:26
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use App\Model\ClientAccount;
use App\Model\SystemAccount;
use App\Model\TransferStepsConfiguration\JointAccountConfiguration;
use App\Model\TransferStepsConfiguration\PersonalAccountConfiguration;
use App\Model\TransferStepsConfiguration\RetirementAccountConfiguration;
use App\Model\TransferStepsConfiguration\RothIraAccountConfiguration;
use App\Model\TransferStepsConfiguration\TraditionalIraAccountConfiguration;
use App\Model\TransferStepsConfigurationInterface;
use App\Manager\AccountDocusignManager;

class TransferScreenStepManager
{
    /**
     * @var AccountDocusignManager
     */
    private $adm;

    public function __construct(AccountDocusignManager $adm)
    {
        $this->adm = $adm;
    }

    /**
     * Get next account transfer screen step by current step.
     *
     * @param ClientAccount $account
     * @param string        $currentStep
     *
     * @return string
     */
    public function getNextStep(ClientAccount $account, $currentStep)
    {
        $configuration = $this->getStepsConfiguration($account);

        return $configuration->getNextStep($currentStep);
    }

    /**
     * Get previous account transfer screen step by current step.
     *
     * @param ClientAccount $account
     * @param string        $currentStep
     *
     * @return string
     */
    public function getPreviousStep(ClientAccount $account, $currentStep)
    {
        $configuration = $this->getStepsConfiguration($account);

        return $configuration->getPreviousStep($currentStep);
    }

    /**
     * Get transfer screen steps configuration object by client account.
     *
     * @param ClientAccount $account
     *
     * @return TransferStepsConfigurationInterface
     *
     * @throws \InvalidArgumentException
     */
    private function getStepsConfiguration(ClientAccount $account)
    {
        $type = $account->getSystemType();
        switch ($type) {
            case SystemAccount::TYPE_PERSONAL_INVESTMENT:
                $stepsConfiguration = new PersonalAccountConfiguration($this->adm, $account);
                break;

            case SystemAccount::TYPE_JOINT_INVESTMENT:
                $stepsConfiguration = new JointAccountConfiguration($this->adm, $account);
                break;

            case SystemAccount::TYPE_TRADITIONAL_IRA:
                $stepsConfiguration = new TraditionalIraAccountConfiguration($this->adm, $account);
                break;

            case SystemAccount::TYPE_ROTH_IRA:
                $stepsConfiguration = new RothIraAccountConfiguration($this->adm, $account);
                break;

            case SystemAccount::TYPE_RETIREMENT:
                $stepsConfiguration = new RetirementAccountConfiguration($this->adm, $account);
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Invalid value of account %s .', $type)
                );
                break;
        }

        return $stepsConfiguration;
    }
}
