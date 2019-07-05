<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.03.13
 * Time: 14:33
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use App\Entity\ClientAccount;
use App\Entity\SystemAccount;

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
