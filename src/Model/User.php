<?php

namespace App\Model;

use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Context\ExecutionContext;
use App\Entity\Group;

class User extends BaseUser
{
    /**
     * @var \DateTime
     */
    protected $password_expired_at;

    /**
     * @param \App\Entity\Profile
     */
    protected $profile;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $riaDashboardBoxes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $clientActivitySummaries;

    /**
     * Get profile.
     *
     * @return \App\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Get user first name.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getProfile() ? $this->getProfile()->getFirstName() : null;
    }

    /**
     * Get user last name.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getProfile() ? $this->getProfile()->getLastName() : null;
    }

    public function setPlainPassword($password, $expiredAfter = null)
    {
        $this->plainPassword = $password;

        //TODO need remove to the configuration
        if (!$expiredAfter) {
            $expiredAfter = 'now +6 month';
        }

        $this->setPasswordExpiredAt(new \DateTime($expiredAfter));

        return $this;
    }

    /**
     * Set password_expired_at.
     *
     * @param \DateTime $passwordExpiredAt
     *
     * @return User
     */
    public function setPasswordExpiredAt($passwordExpiredAt)
    {
        $this->password_expired_at = $passwordExpiredAt;

        return $this;
    }

    /**
     * Get password_expired_at.
     *
     * @return \DateTime
     */
    public function getPasswordExpiredAt()
    {
        return $this->password_expired_at;
    }

    /**
     * Checks whether the password has expired.
     *
     * @return bool
     */
    public function isPasswordExpired()
    {
        return $this->getPasswordExpiredAt() instanceof \DateTime &&
            $this->getPasswordExpiredAt()->getTimestamp() < time();
    }

    /**
     * Validate password.
     *
     * @param ExecutionContext $context
     */
    public function isPasswordLegal(ExecutionContext $context)
    {
        $password = $this->getPlainPassword();

        if ($password === $this->getUsername()) {
            $context->addViolationAt('plainPassword', 'Email and password cant be the same', [], null);
        }

        if (strlen($password) > 0) {
            if (
                !(
                    strlen($password) >= 6 // at least 6 chars
                    && preg_match('`[A-Z]{1,}`', $password) // at least 1 uppercase
                    && preg_match('`[0-9]{1,}`', $password) // at least 1 number
                )
            ) {
                $context->addViolationAt('plainPassword', 'Password is not valid!', [], null);
            }

            if (strlen(stristr($password, $this->getFirstName())) > 0) {
                $context->addViolationAt('plainPassword', 'Password cannot contain your name', [], null);
            }

            if (strlen(stristr($password, $this->getLastName())) > 0) {
                $context->addViolationAt('plainPassword', 'Password cannot contain your name', [], null);
            }
        }
    }

    /**
     * Generate temporary password.
     *
     * @param int $length
     *
     * @return string
     */
    public function generateTemporaryPassword($length = 16)
    {
        $chars = [
            'abcdefghijklmnopqrstuvwxyz',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '0123456789',
            '!@#$%^&*()[];:<>/+-.,',
        ];

        $counts = [
            mb_strlen($chars[0]) - 1,
            mb_strlen($chars[1]) - 1,
            mb_strlen($chars[2]) - 1,
            mb_strlen($chars[3]) - 1,
        ];

        $result = '';
        for ($i = 0; $i < 4; ++$i) {
            $result .= mb_substr($chars[$i], rand(0, $counts[$i]), 1);
        }

        for ($i = 4; $i < $length; ++$i) {
            if ($i === (int) $length / 2) {
                $category = rand(2, 3);
            } else {
                $category = rand(0, 3);
            }

            $result .= mb_substr($chars[$category], rand(0, $counts[$category]), 1);
        }

        return $result;
    }

    /**
     * Is user is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN');
    }

    protected function addRiaDashboardBoxe(\App\Entity\RiaDashboardBox $riaDashboardBox)
    {
        $this->riaDashboardBoxes[] = $riaDashboardBox;

        return $this;
    }

    protected function removeRiaDashboardBoxe(\App\Entity\RiaDashboardBox $riaDashboardBox)
    {
        $this->riaDashboardBoxes->removeElement($riaDashboardBox);
    }

    public function addRiaDashboardBox(\App\Entity\RiaDashboardBox $riaDashboardBox)
    {
        return $this->addRiaDashboardBoxe($riaDashboardBox);
    }

    public function removeRiaDashboardBox(\App\Entity\RiaDashboardBox $riaDashboardBox)
    {
        $this->removeRiaDashboardBoxe($riaDashboardBox);
    }

    /**
     * Set client status as prospect.
     *
     * @return $this
     */
    public function setStatusProspect()
    {
        $this->profile->setStatusProspect();

        return $this;
    }

    /**
     * Is client has status prospect.
     *
     * @return bool
     */
    public function hasStatusProspect()
    {
        if ($this->hasRole('ROLE_CLIENT')) {
            return $this->profile->hasStatusProspect();
        }

        return false;
    }

    /**
     * Set client status as client.
     *
     * @return $this
     */
    public function setStatusClient()
    {
        $this->profile->setStatusClient();

        return $this;
    }

    /**
     * Is client has status client.
     *
     * @return bool
     */
    public function hasStatusClient()
    {
        if ($this->hasRole('ROLE_CLIENT')) {
            return $this->profile->hasStatusClient();
        }

        return false;
    }

    /**
     * Get client status.
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function getClientStatus()
    {
        if (!$this->hasRole('ROLE_CLIENT')) {
            throw new \RuntimeException('User dose not have role: ROLE_CLIENT');
        }

        return $this->profile->getClientStatus();
    }

    /**
     * Get client status as string.
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function getClientStatusAsString()
    {
        if (!$this->hasRole('ROLE_CLIENT')) {
            throw new \RuntimeException('User dose not have role: ROLE_CLIENT');
        }

        return $this->profile->getClientStatusAsString();
    }

    /**
     * Add clientActivitySummary.
     *
     * @param \App\Entity\ClientActivitySummary $clientActivitySummary
     *
     * @return User
     */
    protected function addClientActivitySummarie(\App\Entity\ClientActivitySummary $clientActivitySummary)
    {
        $this->clientActivitySummaries[] = $clientActivitySummary;

        return $this;
    }

    public function addClientActivitySummary(\App\Entity\ClientActivitySummary $clientActivitySummary)
    {
        return $this->addClientActivitySummarie($clientActivitySummary);
    }

    /**
     * Remove clientActivitySummary.
     *
     * @param \App\Entity\ClientActivitySummary $clientActivitySummary
     */
    protected function removeClientActivitySummarie(\App\Entity\ClientActivitySummary $clientActivitySummary)
    {
        $this->clientActivitySummaries->removeElement($clientActivitySummary);
    }

    public function removeClientActivitySummary(\App\Entity\ClientActivitySummary $clientActivitySummary)
    {
        $this->removeClientActivitySummary($clientActivitySummary);
    }

    public function isHaveSuggestedPortfolio()
    {
        if ($this->hasRole('ROLE_CLIENT')) {
            $suggestedPortfolio = $this->getProfile()->getSuggestedPortfolio();

            if ($suggestedPortfolio && $suggestedPortfolio->getOwner()->hasRole('ROLE_RIA')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed|string
     */
    public function getAssignedGroupName()
    {
        if ($this->getGroups()->isEmpty()) {
            return Group::GROUP_NAME_ALL;
        } else {
            return $this->getGroups()->first()->getName();
        }
    }

    public function getGroupIds()
    {
        $ids = [];
        foreach ($this->getGroups() as $group) {
            $ids[] = $group->getId();
        }

        return $ids;
    }
}
