<?php

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;
use App\Manager\CeModelManager;
use App\Manager\ClientPortfolioManager;
use App\Entity\Profile;

class ChooseClientPortfolioFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $modelManager = $this->getOption('model_manager');
        $clientPortfolioManager = $this->getOption('client_portfolio_manager');

        if (!($modelManager instanceof CeModelManager)) {
            throw new \InvalidArgumentException(sprintf('Option model_manager must be instance of Manager\CeModelManager'));
        }

        if (!($clientPortfolioManager instanceof ClientPortfolioManager)) {
            throw new \InvalidArgumentException(sprintf('Option client_portfolio_manager must be instance of Manager\ClientPortfolioManager'));
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
