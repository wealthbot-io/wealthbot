<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.09.13
 * Time: 14:00
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClientAccountDocusign;
use App\Entity\TransferInformation;
use App\Model\AccountGroup;
use App\Model\ClientAccount;
use App\Docusign\DocusignChecker;

class AccountDocusignManager
{
    private $om;
    private $class;
    private $repository;

    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Get object manager.
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * Find client account docusign.
     *
     * @param int $id
     *
     * @return object
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Find client account docusign object by criteria.
     *
     * @param array $criteria
     *
     * @return ClientAccountDocusign
     */
    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find client account docusign objects by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return mixed
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Update client account docusign is_used attribute in DB.
     *
     * @param TransferInformation $transferInformation
     * @param $conditions
     */
    public function updateIsDocusignUsed(TransferInformation $transferInformation, $conditions)
    {
        $isUsed = $this->isDocusignAllowed($transferInformation, $conditions);
        $this->setIsUsedDocusign($transferInformation->getClientAccount(), $isUsed);
    }

    /**
     * Check is docusign allowed for transfer client account by conditions.
     *
     * @param TransferInformation $transferInformation
     * @param $conditions
     *
     * @return bool
     */
    public function isDocusignAllowed(TransferInformation $transferInformation, $conditions)
    {
        $checker = new DocusignChecker($conditions);

        return $checker->checkConditions($transferInformation);
    }

    /**
     * Set isUsed attribute for transfer client account in DB.
     * If record with $accountId does not exist in DB create new.
     *
     * @param ClientAccount $account
     * @param bool          $isUsed
     */
    public function setIsUsedDocusign(ClientAccount $account, $isUsed)
    {
        $accountDocusign = $this->findOneBy(['client_account_id' => $account->getId()]);

        if (!$accountDocusign) {
            $accountDocusign = new ClientAccountDocusign();
            $accountDocusign->setClientAccount($account);
        }

        $accountDocusign->setIsUsed($isUsed);

        ///$this->om->persist($accountDocusign);
        //$this->om->flush();
    }

    /**
     * Get is used docusign by account_id.
     * If record does not exist returns false.
     *
     * @param int $accountId
     *
     * @return bool
     */
    public function isUsedDocusign($accountId)
    {
        $accountDocusign = $this->findOneBy(['client_account_id' => $accountId]);
        if ($accountDocusign) {
            return $accountDocusign->getIsUsed();
        }

        return false;
    }

    /**
     * Returns true if transfer account cannot use electronically signing
     * and ria does not allow non electronically signing.
     *
     * @param ClientAccount $account
     *
     * @return bool
     */
    public function hasElectronicallySignError(ClientAccount $account)
    {
        if ($account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION) &&
            !$this->isUsedDocusign($account->getId())
        ) {
            $ria = $account->getClient()->getRia();
            $riaCompanyInfo = $ria->getRiaCompanyInformation();

            if ($riaCompanyInfo && !$riaCompanyInfo->getAllowNonElectronicallySigning()) {
                return true;
            }
        }

        return false;
    }
}
