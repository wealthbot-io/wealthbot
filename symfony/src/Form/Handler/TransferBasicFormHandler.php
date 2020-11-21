<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 22.05.13
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ClientAccount;
use App\Model\AccountOwnerInterface;
use App\Model\UserAccountOwnerAdapter;

class TransferBasicFormHandler
{
    protected $request;
    protected $form;
    protected $em;

    public function __construct(FormInterface $form, Request $request, EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
    }

    public function process(ClientAccount $account, $isPreSaved = false)
    {
        if ($this->request->isMethod('post')) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($account, $isPreSaved);

                return true;
            }
        }

        return false;
    }

    private function onSuccess(ClientAccount $account, $isPreSaved = false)
    {
        /** @var AccountOwnerInterface $data */
        $data = $this->form->getData();
        $isPrimaryApplicant = ($data instanceof UserAccountOwnerAdapter);

        if ($isPrimaryApplicant) {
            $account->setProcessStep(ClientAccount::PROCESS_STEP_STARTED_TRANSFER);
            $account->setStepAction(ClientAccount::STEP_ACTION_BASIC);
        } else {
            $account->setStepAction(ClientAccount::STEP_ACTION_ADDITIONAL_BASIC);
        }

        $account->setIsPreSaved($isPreSaved);

        $this->em->persist($data->getObjectToSave());
        $this->em->persist($account);

        $this->em->flush();
    }
}
