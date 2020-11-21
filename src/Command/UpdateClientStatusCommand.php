<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Profile;
use App\Entity\User;

class UpdateClientStatusCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:client:update-statuses')
            ->setDescription('Update client_status column in user_profiles table for client')
            ->setHelp(
                <<<EOT
The <info>rx:client:update-statuses</info> command update client_status column in user_profiles table for client:

  <info>php app/console rx:client:update-statuses</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\User');

        $clients = $repository->findAllClients();
        $clientCounter = 0;

        /** @var User $client */
        foreach ($clients as $client) {
            $profile = $client->getProfile();

            $output->write(sprintf('Update client: %d ', $client->getId()));

            if ($client->getRegistrationStep() < 7) {
                $profile->setClientStatus(Profile::CLIENT_STATUS_PROSPECT);
            } else {
                $profile->setClientStatus(Profile::CLIENT_STATUS_CLIENT);
            }

            $output->write(sprintf('set status: %s.', $profile->getClientStatusAsString()));
            $output->writeln('');

            $em->persist($client);
            ++$clientCounter;
        }

        $em->flush();

        $output->writeln(sprintf('Complete. Clients updated %d.', $clientCounter));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
