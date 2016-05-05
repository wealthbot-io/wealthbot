<?php

namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;
use Wealthbot\AdminBundle\Manager\CeModelManager;
use Wealthbot\ClientBundle\Manager\ClientPortfolioManager;
use Wealthbot\UserBundle\Entity\Profile;

class ChooseClientPortfolioFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $modelManager = $this->getOption('model_manager');
        $clientPortfolioManager = $this->getOption('client_portfolio_manager');

        if (!($modelManager instanceof CeModelManager)) {
            throw new \InvalidArgumentException(sprintf('Option model_manager must be instance of Wealthbot\AdminBundle\Manager\CeModelManager'));
        }

        if (!($clientPortfolioManager instanceof ClientPortfolioManager)) {
            throw new \InvalidArgumentException(sprintf('Option client_portfolio_manager must be instance of Wealthbot\ClientBundle\Manager\ClientPortfolioManager'));
        }

        /** @var Profile $profile */
        $profile = $this->form->getData();
//        $modelManager->setClientPortfolio($profile->getSuggestedPortfolio(), $profile->getUser(), $clientPortfolioManager);
//        $profile->getSuggestedPortfolio();
//
//        $this->em->persist($profile);
//        $this->em->flush();

        $clientPortfolioManager->acceptApprovedPortfolio($profile->getUser());
    }
}
