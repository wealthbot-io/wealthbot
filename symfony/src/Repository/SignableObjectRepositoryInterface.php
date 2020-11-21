<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 20.11.13
 * Time: 17:11.
 */

namespace App\Repository;

interface SignableObjectRepositoryInterface
{
    /**
     * Is object has completed document signature for application.
     *
     * @param int $applicationId
     *
     * @return bool
     */
    public function isApplicationSigned($applicationId);
}
