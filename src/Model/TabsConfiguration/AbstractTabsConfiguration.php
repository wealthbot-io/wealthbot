<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 01.05.14
 * Time: 15:45.
 */

namespace App\Model\TabsConfiguration;

use App\Entity\AdvisorCode;
use App\Entity\RiaCompanyInformation;
use App\Model\TabCollection;
use App\Model\TabsConfigurationInterface;

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
