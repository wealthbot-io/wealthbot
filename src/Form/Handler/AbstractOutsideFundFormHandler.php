<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.03.13
 * Time: 13:28
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\SecurityAssignment;
use App\Entity\AccountOutsideFund;
use App\Entity\ClientAccount;
use Repository\AccountOutsideFundRepository;

abstract class AbstractOutsideFundFormHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
    }

    public function process(ClientAccount $account)
    {
        if ($this->request->isMethod('post')) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->success($account);

                return true;
            }
        }

        return false;
    }

    protected function success(ClientAccount $account)
    {
        $data = $this->form->getData();

        $this->em->persist($data);
        $this->em->flush();

        $isPreferred = false;
        if ($this->form->has('is_preferred')) {
            $isPreferred = $this->form->get('is_preferred')->getdata();
        }

        $this->checkAccountAssociation($account, $data, $isPreferred);
    }

    /**
     * Check if account outside fund exist and create new if it does not exist.
     *
     * @param ClientAccount      $account
     * @param SecurityAssignment $securityAssignment
     * @param $isPreferred
     *
     * @return mixed
     */
    abstract protected function checkAccountAssociation(ClientAccount $account, SecurityAssignment $securityAssignment, $isPreferred);

    /**
     * Check if account outside fund is exist.
     *
     * @param $accountId
     * @param $securityId
     *
     * @return AccountOutsideFund|null
     */
    protected function existAccountAssociation($accountId, $securityId)
    {
        /** @var $repo AccountOutsideFundRepository */
        $repo = $this->em->getRepository('App\Entity\AccountOutsideFund');
        $exist = $repo->findOneBy(['account_id' => $accountId, 'security_assignment_id' => $securityId]);

        return $exist;
    }

    /**
     * Create new account outside fund.
     *
     * @param ClientAccount      $account
     * @param SecurityAssignment $securityAssignment
     * @param $isPreferred
     *
     * @return AccountOutsideFund
     */
    protected function createAccountAssociation(ClientAccount $account, SecurityAssignment $securityAssignment, $isPreferred)
    {
        $accountAssociation = new AccountOutsideFund();
        $accountAssociation->setAccount($account);
        $accountAssociation->setSecurityAssignment($securityAssignment);
        $accountAssociation->setIsPreferred($isPreferred);

        $this->em->persist($accountAssociation);
        $this->em->flush();

        return $accountAssociation;
    }

    protected function checkSecurityExist($security)
    {
    }
}
