<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebalancerCommand extends ContainerAwareCommand
{


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:rebalancer')
            ->setDescription('Wealthbot Rebalancer Command')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Rebalancer started');
        $this->getContainer()->get('App\Api\Rebalancer')->rebalance();

        $output->writeln('Rebalancer finished');
    }
}
