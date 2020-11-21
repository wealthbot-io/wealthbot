<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\AccountGroupType;

/**
 * @author Maxim Belyakov
 */
class SwapClientAccountTypesCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:client:swap-client-account-types')
            ->setDescription('Swap client account types by id')
            ->addArgument(
                'id1',
                InputArgument::REQUIRED,
                'swap id1'
            )
            ->addArgument(
                'id2',
                InputArgument::REQUIRED,
                'swap id2'
            )
            ->setHelp(
                <<<EOT
  <info>php app/console rx:client:swap-client-account-types</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id1 = $input->getArgument('id1');
        $id2 = $input->getArgument('id2');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $accountType1 = $em->getRepository('App\Entity\AccountType')->find($id1);
        $accountType2 = $em->getRepository('App\Entity\AccountType')->find($id2);

        if (!$accountType1 || !$accountType2) {
            $output->writeln(sprintf('Client account types not found'));
        } else {
            $accountGroups1 = $em->getRepository('App\Entity\AccountGroupType')->findBy(['type_id' => $id1]);
            $accountGroups2 = $em->getRepository('App\Entity\AccountGroupType')->findBy(['type_id' => $id2]);
            foreach ($accountGroups1 as $accountGroup1) {
                /* @var $accountGroup1 AccountGroupType*/
                $accountGroup1->setTypeId($accountType2->getId());
                $em->persist($accountGroup1);
                $output->writeln(sprintf('Client account group types %d update type_id %d to %d', $accountGroup1->getId(), $accountType1->getId(), $accountType2->getId()));
            }
            $output->writeln('');
            foreach ($accountGroups2 as $accountGroup2) {
                $accountGroup2->setTypeId($accountType1->getId());
                $em->persist($accountGroup2);
                $output->writeln(sprintf('Client account group types %d update type_id %d to %d', $accountGroup2->getId(), $accountType2->getId(), $accountType1->getId()));
            }
            $output->writeln('');
            $tmpName = $accountType1->getName();
            $accountType1->setName($accountType2->getName());
            $em->persist($accountType1);
            $accountType2->setName($tmpName);
            $em->persist($accountType2);
            $output->writeln(sprintf('Clients updated %s', $accountType1->getName()));
            $output->writeln(sprintf('Clients updated %s', $accountType2->getName()));
            $em->flush();
        }
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
