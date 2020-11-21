<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.09.13
 * Time: 14:01
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Model\Tab\AbstractTab;

interface RecipientInterface
{
    const TYPE_SIGNER = 'Signer';
    const TYPE_AGENT = 'Agent';
    const TYPE_EDITOR = 'Editor';
    const TYPE_CARBON_COPY = 'CarbonCopy';
    const TYPE_CERTIFIED_DELIVERY = 'CertifiedDelivery';
    const TYPE_IN_PERSON_SIGNER = 'InPersonSigner';

    /**
     * Set recipient email.
     *
     * @param string $email
     *
     * @return mixed
     */
    public function setEmail($email);

    /**
     * Get recipient email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set recipient name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name);

    /**
     * Get recipient name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set recipient role name.
     *
     * @param string $roleName
     *
     * @return mixed
     */
    public function setRoleName($roleName);

    /**
     * Get recipient role name.
     *
     * @return string
     */
    public function getRoleName();

    /**
     * Set recipient client user id.
     *
     * @param int $clientUserId
     *
     * @return mixed
     */
    public function setClientUserId($clientUserId);

    /**
     * Get recipient client user id.
     *
     * @return int
     */
    public function getClientUserId();

    /**
     * Set recipient type.
     *
     * @param string $type
     *
     * @return mixed
     */
    public function setType($type);

    /**
     * Get recipient type.
     *
     * @return string
     */
    public function getType();

    /**
     * Set tab collection.
     *
     * @param \App\Model\TabCollection $tabs
     */
    public function setTabs(TabCollection $tabs);

    /**
     * Add tab element.
     *
     * @param AbstractTab $tab
     */
    public function addTab(AbstractTab $tab);

    /**
     * Remove tab element.
     *
     * @param AbstractTab $tab
     *
     * @return bool
     */
    public function removeTab(AbstractTab $tab);

    /**
     * Get tab collection.
     *
     * @return \App\Model\TabCollection
     */
    public function getTabs();
}
