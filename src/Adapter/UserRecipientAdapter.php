<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.09.13
 * Time: 16:48
 * To change this template use File | Settings | File Templates.
 */

namespace App\Adapter;

use App\Exception\InvalidRecipientTypeException;
use App\Model\Recipient;
use App\Model\RecipientInterface;
use App\Model\Tab\AbstractTab;
use App\Model\TabCollection;
use App\Entity\User;

class UserRecipientAdapter implements RecipientInterface
{
    /**
     * @param \App\Entity\User
     */
    private $user;

    /**
     * @var string
     */
    private $type;

    /**
     * @var TabCollection
     */
    private $tabs;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->type = RecipientInterface::TYPE_SIGNER;
        $this->tabs = new TabCollection();
    }

    /**
     * Get recipient email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->user->getEmail();
    }

    /**
     * Get recipient name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->user->getFullName();
    }

    /**
     * Get recipient role name.
     *
     * @return string
     */
    public function getRoleName()
    {
        if ($this->user->hasRole('ROLE_CLIENT')) {
            return 'Client';
        }

        if ($this->user->hasRole('ROLE_RIA')) {
            return 'Advisor';
        }

        return '';
    }

    /**
     * Get recipient client user id.
     *
     * @return int
     */
    public function getClientUserId()
    {
        return 'primary_'.$this->user->getId();
    }

    /**
     * Set tab collection.
     *
     * @param \App\Model\TabCollection $tabs
     */
    public function setTabs(TabCollection $tabs)
    {
        $this->tabs = $tabs;
    }

    /**
     * Add tab element.
     *
     * @param AbstractTab $tab
     */
    public function addTab(AbstractTab $tab)
    {
        $this->tabs->addTab($tab);
    }

    /**
     * Remove tab element.
     *
     * @param AbstractTab $tab
     *
     * @return bool
     */
    public function removeTab(AbstractTab $tab)
    {
        return $this->tabs->removeTab($tab);
    }

    /**
     * Get tab collection.
     *
     * @return \App\Model\TabCollection
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * Set recipient type.
     *
     * @param string $type
     *
     * @return mixed|void
     *
     * @throws InvalidRecipientTypeException
     */
    public function setType($type)
    {
        if (!in_array($type, Recipient::getTypeChoices())) {
            throw new InvalidRecipientTypeException(sprintf('Invalid recipient type: %s', $type));
        }

        $this->type = $type;
    }

    /**
     * Get recipient type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set recipient email.
     *
     * @param string $email
     *
     * @return mixed
     */
    public function setEmail($email)
    {
    }

    /**
     * Set recipient name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name)
    {
    }

    /**
     * Set recipient role name.
     *
     * @param string $roleName
     *
     * @return mixed
     */
    public function setRoleName($roleName)
    {
    }

    /**
     * Set recipient client user id.
     *
     * @param int $clientUserId
     *
     * @return mixed
     */
    public function setClientUserId($clientUserId)
    {
    }
}
