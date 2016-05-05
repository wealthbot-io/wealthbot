<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.03.13
 * Time: 14:33
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Manager;

use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\SystemAccount;

interface SystemAccountManagerInterface
{
    /**
     * Create new system account for client account.
     *
     * @param ClientAccount $clientAccount
     *
     * @return SystemAccount
     */
    public function createSystemAccountForClientAccount(ClientAccount $clientAccount);
}
