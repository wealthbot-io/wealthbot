<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 01.05.14
 * Time: 15:45.
 */

namespace Wealthbot\SignatureBundle\Model\TabsConfiguration;

use Wealthbot\RiaBundle\Entity\AdvisorCode;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\SignatureBundle\Model\TabCollection;
use Wealthbot\SignatureBundle\Model\TabsConfigurationInterface;

abstract class AbstractTabsConfiguration implements TabsConfigurationInterface
{
    /**
     * Generate collection of tabs.
     *
     * @return TabCollection
     */
    abstract public function generate();

    /**
     * Get advisor code.
     *
     * @param RiaCompanyInformation $companyInformation
     *
     * @return string
     */
    protected function getAdvisorCode(RiaCompanyInformation $companyInformation = null)
    {
        $code = '';

        if (null !== $companyInformation) {
            /** @var AdvisorCode $advisorCode */
            foreach ($companyInformation->getAdvisorCodes() as $advisorCode) {
                if ($advisorCode->getCustodian() === $companyInformation->getCustodian()) {
                    $code = $advisorCode->getName();
                    break;
                }
            }
        }

        return $code;
    }
}
