<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.12.12
 * Time: 13:42
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Session\Session;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\ClientBundle\Form\Handler\ClientQuestionsFormHandler as BaseHandler;
use Wealthbot\UserBundle\Entity\User;

class RiskQuestionsFormHandler extends BaseHandler
{
    protected function preProcess(User $user)
    {
    }

    /**
     * Process suggested portfolio.
     *
     * @param User    $user
     * @param CeModel $suggestedModel
     * @param $withdrawAge
     *
     * @throws \InvalidArgumentException
     */
    protected function processSuggestedPortfolio(User $user, CeModel $suggestedModel, $withdrawAge)
    {
        $session = $this->getOption('session');
        if (!($session instanceof Session)) {
            throw new \InvalidArgumentException('Option session must be instance of Session.');
        }

        $session->set('ria.risk_profiling.suggested_portfolio', $suggestedModel->getId());
    }

    /**
     * Get age of the client.
     *
     * @param \Wealthbot\UserBundle\Entity\User $user
     *
     * @return int
     */
    protected function getClientAge(User $user)
    {
        /** @var $birthDate \DateTime */
        $birthDate = $this->form->get('client_birth_date')->getData();

        return $birthDate->diff(new \DateTime('now'))->y;
    }
}
