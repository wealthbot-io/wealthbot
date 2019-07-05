<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\SubclassRepository;

/**
 * @author Maxim Belyakov
 */
class AddSubclassesCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:client:add-subclasses')
            ->setDescription('Add subclasses to the exists clients.')
            ->setHelp(
                <<<EOT
The <info>rx:client:add-subclasses</info> command copied RIA subclasses to the client subclasses:

  <info>php app/console rx:client:add-subclasses</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $subclassRepo SubclassRepository */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $subclassRepo = $em->getRepository('App\Entity\Subclass');

        $q = $em->getRepository('App\Entity\User')
            ->createQueryBuilder('u')
            ->leftJoin('u.clientSubclasses', 'cs')
            ->leftJoin('u.profile', 'p')
            ->where("u.roles LIKE '%ROLE_CLIENT%'")
            ->andWhere('cs.id IS NULL')
            ->groupBy('u.id')
        ;

        $clients = $q->getQuery()->getResult();

        $clientCounter = 0;
        foreach ($clients as $client) {
            $ria = $client->getRia();
            $subclassRepo->saveClientSubclasses($client, $ria);

            ++$clientCounter;
        }
        $em->flush();
        $em->clear();

        $output->writeln(sprintf('Clients updated %d', $clientCounter));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
