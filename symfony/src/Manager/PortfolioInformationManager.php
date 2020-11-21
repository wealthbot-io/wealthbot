<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.05.13
 * Time: 16:10
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Manager\FeeManager;
use App\Model\CeModelInterface;
use App\Model\PortfolioInformation;
use App\Entity\User;

class PortfolioInformationManager
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * @var FeeManager
     */
    protected $feeManager;

    public function __construct(EntityManager $em, FeeManager $feeManager)
    {
        $this->em = $em;
        $this->feeManager = $feeManager;
    }

    public function getPortfolioInformation(User $user, CeModelInterface $model, $isQualified = false)
    {
        if ($user->hasRole('ROLE_CLIENT')) {
            $ria = $user->getRia();
        } else {
            $ria = $user;
        }

        $portfolioInformation = new PortfolioInformation();
        $portfolioInformation->setUser($user);
        $portfolioInformation->setModel($model);
        $portfolioInformation->setIsQualifiedModel($isQualified);
        $portfolioInformation->setFees($this->feeManager->getClientFees($ria));

        if ($model->getOwner()->hasRole('ROLE_RIA')) {
            $transactionCommissionFees = $this->em->getRepository('App\Entity\SecurityAssignment')->findMinAndMaxTransactionFeeForModel($model->getParentId());
            $portfolioInformation->setTransactionCommissionFees(array_values($transactionCommissionFees));
        }

        return $portfolioInformation;
    }
}
