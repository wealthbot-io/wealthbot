<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.10.13
 * Time: 19:01
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\SignatureBundle\Model;

use Wealthbot\ClientBundle\Entity\Beneficiary;
use Wealthbot\ClientBundle\Entity\Distribution;
use Wealthbot\ClientBundle\Model\SystemAccount;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\AccountTransferForm;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\BeneficiaryDesignationForm;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\CheckRequest;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\ElectronicFundsTransferForm;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\IraAccountApplication;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\IraDistributionForm;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\PersonalAccountApplication;
use Wealthbot\SignatureBundle\Model\TabsConfiguration\WireInstructions;

class AccountTabsConfigurationFactory implements TabsConfigurationFactoryInterface
{
    /** @var \Wealthbot\SignatureBundle\Model\SignableInterface */
    private $signableObject;

    public function __construct(SignableInterface $object)
    {
        $this->signableObject = $object;
    }

    /**
     * Create tab configuration object.
     *
     * @return TabsConfigurationInterface
     *
     * @throws \InvalidArgumentException
     */
    public function create()
    {
        $type = $this->signableObject->getDocumentSignatureType();
        $account = $this->signableObject->getClientAccount();
        $accountType = $account->getSystemType();

        switch ($type) {
            case DocumentSignature::TYPE_OPEN_OR_TRANSFER_ACCOUNT:
                if ($accountType === SystemAccount::TYPE_ROTH_IRA ||
                    $accountType === SystemAccount::TYPE_TRADITIONAL_IRA
                ) {
                    $configuration = new IraAccountApplication($account);
                } else {
                    $configuration = new PersonalAccountApplication($account);
                }

                break;

            case DocumentSignature::TYPE_TRANSFER_INFORMATION:
                $configuration = new AccountTransferForm($account);
                break;

            case DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION:
            case DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION:
                $configuration = new ElectronicFundsTransferForm($this->signableObject);
                break;

            case DocumentSignature::TYPE_AUTO_DISTRIBUTION:
                if ($accountType === SystemAccount::TYPE_ROTH_IRA ||
                    $accountType === SystemAccount::TYPE_TRADITIONAL_IRA
                ) {
                    $configuration = new IraDistributionForm($this->signableObject);
                } else {
                    $configuration = new ElectronicFundsTransferForm($this->signableObject);
                }

                break;

            case DocumentSignature::TYPE_CHANGE_BENEFICIARY:
                if (!($this->signableObject instanceof Beneficiary)) {
                    throw new \InvalidArgumentException(sprintf('Object must be instance of Beneficiary.'));
                }

                $configuration = new BeneficiaryDesignationForm($this->signableObject);

                break;

            case DocumentSignature::TYPE_ONE_TIME_DISTRIBUTION:
                if ($accountType === SystemAccount::TYPE_ROTH_IRA ||
                    $accountType === SystemAccount::TYPE_TRADITIONAL_IRA
                ) {
                    $configuration = new IraDistributionForm($this->signableObject);
                } else {
                    if (!($this->signableObject instanceof Distribution) || !$this->signableObject->isOneTime()) {
                        throw new \InvalidArgumentException(sprintf('Object must be one-time distribution.'));
                    }

                    if (Distribution::TRANSFER_METHOD_RECEIVE_CHECK === $this->signableObject->getTransferMethod()) {
                        $configuration = new CheckRequest($this->signableObject);
                    } elseif (Distribution::TRANSFER_METHOD_WIRE_TRANSFER === $this->signableObject->getTransferMethod()) {
                        $configuration = new WireInstructions($this->signableObject);
                    } else {
                        $configuration = new ElectronicFundsTransferForm($this->signableObject);
                    }
                }

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Invalid type: %s for signable document.', $type));
                break;
        }

        return $configuration;
    }
}
