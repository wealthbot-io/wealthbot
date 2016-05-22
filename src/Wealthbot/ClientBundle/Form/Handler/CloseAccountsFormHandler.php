<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.04.13
 * Time: 18:20
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\NotValidException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\ClientBundle\Entity\ClosingAccountHistory;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Repository\SystemAccountRepository;
use Wealthbot\MailerBundle\Mailer\TwigSwiftMailer;
use Wealthbot\UserBundle\Entity\User;

class CloseAccountsFormHandler
{
    private $form;
    private $request;
    private $em;
    private $mailer;

    /** @var SystemAccountRepository $repo */
    private $repo;

    /** @var array */
    private $savedObjects;

    public function __construct(Form $form, Request $request, EntityManager $em, TwigSwiftMailer $mailer)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->mailer = $mailer;

        $this->repo = $this->em->getRepository('WealthbotClientBundle:SystemAccount');
        $this->savedObjects = [];
    }

    /**
     * Submit form process.
     *
     * @param User $client
     *
     * @return bool
     *
     * @throws NotValidException
     */
    public function process(User $client)
    {
        if ($this->request->isMethod('post')) {
            $this->form->handleRequest($this->request);

            $accountsIds = $this->form->get('accounts_ids')->getData();
            if (!is_array($accountsIds) || empty($accountsIds)) {
                throw new NotValidException('Select accounts which you want to close.');
            } elseif (!$this->repo->isClientAccounts($client->getId(), $accountsIds)) {
                throw new NotValidException('You can not close this accounts.');
            }

            if ($this->form->isValid()) {
                $this->onSuccess($client);

                return true;
            }
        }

        return false;
    }

    /**
     * Save valid data.
     *
     * @param User $client
     */
    private function onSuccess(User $client)
    {
        $accountsIds = $this->form->get('accounts_ids')->getData();
        $messagesObjects = $this->form->get('messages')->getData();

        $messages = [];
        foreach ($messagesObjects as $object) {
            $messages[] = $object->getMessage();
        }

        $accounts = $this->repo->findWhereIdIn($accountsIds);
        foreach ($accounts as $account) {
            $account->setStatus(SystemAccount::STATUS_CLOSED);

            $history = new ClosingAccountHistory();
            $history->setAccount($account);
            $history->setMessages($messages);
            $history->setClosingDate(new \DateTime());

            $this->em->persist($account);
            $this->em->persist($history);

            $this->savedObjects[] = $history;
        }

        $this->em->flush();

        $this->mailer->sendRiaClientClosedAccountsEmail($client, $accounts);
    }

    public function getSavedObjects()
    {
        return $this->savedObjects;
    }
}
