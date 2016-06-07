<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 25.11.13
 * Time: 16:25.
 */

namespace Wealthbot\SignatureBundle\Model;

interface SignableInterface
{
    /**
     * Get client account object.
     *
     * @return \Wealthbot\ClientBundle\Model\ClientAccount
     */
    public function getClientAccount();

    /**
     * Get id of source object.
     *
     * @return mixed
     */
    public function getSourceObjectId();

    /**
     * Get type of document signature.
     *
     * @return string
     */
    public function getDocumentSignatureType();
}
