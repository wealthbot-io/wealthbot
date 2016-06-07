<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.13
 * Time: 13:19
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Model\TransferStepsConfiguration;

use Wealthbot\ClientBundle\Model\ClientAccount;
use Wealthbot\ClientBundle\Model\TransferStepsConfigurationInterface;
use Wealthbot\SignatureBundle\Manager\AccountDocusignManager;

class RetirementAccountConfiguration implements TransferStepsConfigurationInterface
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
        if ($currentStep === ClientAccount::STEP_ACTION_CREDENTIALS) {
            $nextStep = ClientAccount::STEP_ACTION_FINISHED;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value of step: "%s" for account with system_type: "%s"',
                $this->account->getSystemTypeAsString(),
                $currentStep
            ));
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
            case ClientAccount::STEP_ACTION_CREDENTIALS:
                $prevStep = '';
                break;

            case ClientAccount::STEP_ACTION_FINISHED:
                $prevStep = ClientAccount::STEP_ACTION_CREDENTIALS;
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
}
