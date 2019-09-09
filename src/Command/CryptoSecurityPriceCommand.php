<?php

namespace App\Command;

use App\Entity\CeModelEntity;
use App\Entity\ClientAccount;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\ClientQuestionnaireAnswer;
use App\Entity\Lot;
use App\Entity\Position;
use App\Entity\RiskAnswer;
use App\Entity\Security;
use App\Entity\SecurityPrice;
use App\Entity\SystemAccount;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CryptoSecurityPriceCommand extends ContainerAwareCommand
{

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:security:crypto')
            ->setDescription('Wealthbot Crypto Securities')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Update Securities started');

        $output->writeln('Update Securities finished');
    }
}
