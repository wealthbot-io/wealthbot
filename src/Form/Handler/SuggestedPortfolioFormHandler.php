<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 07.03.13
 * Time: 17:27
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;
use App\Entity\ClientAccount;
use App\Manager\ClientPortfolioManager;
use App\Repository\ClientAccountRepository;
use App\Mailer\MailerInterface;
use App\Entity\Profile;

class SuggestedPortfolioFormHandler extends AbstractFormHandler
{
    /** @var ClientPortfolioManager */
    private $clientPortfolioManager;

    public function success()
    {
        $this->clientPortfolioManager = $this->getOption('client_portfolio_manager');
        if (!($this->clientPortfolioManager instanceof ClientPortfolioManager)) {
            throw new \InvalidArgumentException('Option client_portfolio_manager must be instance of ClientPortfolioManager.');
        }

        $profile = $this->form->getConfig()->getOption('client')->getProfile();
        $action = $this->form->get('action_type')->getData();

        $this->unconsolidateAccounts($this->form->get('unconsolidated_ids')->getData());

        if ('submit' === $action) {
            $this->submit($profile);
        } elseif ('update' === $action) {
            $this->update();
        }
    }

    /**
     * Unconsolidate accounts.
     *
     * @param array $accountIds
     */
    private function unconsolidateAccounts($accountIds = null)
    {
        if (is_array($accountIds)) {
            /** @var ClientAccountRepository $repo */
            $repo = $this->em->getRepository('App\Entity\ClientAccount');
            $accounts = $repo->findWhereIdIn($accountIds);

            if (is_array($accounts) && !empty($accounts)) {
                /** @var ClientAccount $consolidator */
                $consolidator = $repo->findConsolidatorWhereIdIn($accountIds);

                if ($consolidator) {
                    $oldConsolidatorId = $consolidator->getId();
                    $needConsolidate = $consolidator->getConsolidatedAccounts();
                } else {
                    $consolidator = $accounts[0]->getConsolidator();

                    $oldConsolidatorId = null;
                    $needConsolidate = [];
                }

                foreach ($accounts as $account) {
                    $consolidator->removeConsolidatedAccount($account);

                    $account->setConsolidator(null);
                    $account->setUnconsolidated(true);

                    if ($oldConsolidatorId) {
                        $needConsolidate->removeElement($account);
                    }
                }

                $this->em->persist($consolidator);
                $this->em->flush();

                if ($oldConsolidatorId) {
                    if (count($needConsolidate) > 1) {
                        /** @var ClientAccount $newConsolidator */
                        $newConsolidator = $repo->findNewConsolidatorForAccounts($consolidator);

                        foreach ($needConsolidate as $item) {
                            if ($item->getId() === $newConsolidator->getId()) {
                                $item->setConsolidator(null);
                            } else {
                                $newConsolidator->addConsolidatedAccount($item);
                                $item->setConsolidator($newConsolidator);

                                $consolidator->removeConsolidatedAccount($item);
                            }
                        }

                        $this->em->persist($newConsolidator);
                    }

                    foreach ($consolidator->getConsolidatedAccounts() as $item) {
                        $consolidator->removeConsolidatedAccount($item);
                        $item->setConsolidator(null);

                        $this->em->persist($item);
                    }

                    $this->em->persist($consolidator);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * Update client suggested portfolio.
     */
    private function update()
    {
        $client = $this->form->get('client')->getData();
        $portfolio = $this->form->get('client')->get('portfolio')->getData();

        $clientPortfolio = $this->clientPortfolioManager->getProposedClientPortfolio($client);
        $clientPortfolio->setPortfolio($portfolio);

        $this->em->persist($clientPortfolio);
        $this->em->flush();
    }

    /**
     * Submit client suggested portfolio.
     *
     * @param Profile $profile
     */
    private function submit(Profile $profile)
    {
        $proposedPortfolio = $this->form->get('client')->get('portfolio')->getData();
        $client = $profile->getUser();
        $riaCompanyInfo = $client->getRiaCompanyInformation();

        //$client->submitFinalPortfolio();

        $this->clientPortfolioManager->approveProposedPortfolio($client, $proposedPortfolio);
        $profile->setRegistrationStep(4);

        // If client account managed is null set ria account managed for client
        if (null === $profile->getClientAccountManaged() && $riaCompanyInfo) {
            $profile->setClientAccountManaged($riaCompanyInfo->getAccountManaged());
        }

        $profile->setStatusClient();
        $this->em->persist($profile);
        $this->em->persist($client);
        $this->em->flush();

        $mailer = $this->getOption('mailer');
        if ($mailer instanceof MailerInterface) {
            try {
                $mailer->sendClientPortfolioIsSubmittedEmail($client);
            } catch (\Exception $e) {
            }
        }
    }
}
