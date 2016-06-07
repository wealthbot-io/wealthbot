<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 15.01.14
 * Time: 19:39.
 */

namespace Wealthbot\ClientBundle\Model;

use Wealthbot\UserBundle\Entity\User;

interface ActivityInterface
{
    /**
     * Get activity message.
     *
     * @return string
     */
    public function getActivityMessage();

    /**
     * Get activity client.
     *
     * @return User
     */
    public function getActivityClient();
}
