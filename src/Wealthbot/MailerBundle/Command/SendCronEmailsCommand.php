<?php

namespace Wealthbot\MailerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCronEmailsCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:mailer:send-cron-emails')
            ->setDescription('Send Cron Emails')
            ->setHelp(<<<EOT
  <info>php app/console rx:mailer:send-cron-emails</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->getConfiguration()->addCustomDatetimeFunction('DATEDIFF', 'Wealthbot\MailerBundle\DQL\DatetimeFunction\DateDiff');

        $mailer = $this->getContainer()->get('wealthbot.mailer');

        $userRepo = $em->getRepository('WealthbotUserBundle:User');

        $output->write('Start sending emails for Advisors which not complete registration');
        $rias = $userRepo->findNotActivatedRiasForSendEmail();
        foreach ($rias as $ria) {
            $mailer->sendRiaNotFinishedRegistrationEmail($ria);
        }
        $output->write(' - success', true);

        $output->write('Start sending emails for Clients which not finished registration');
        $clients = $em->getRepository('WealthbotUserBundle:User')->findNotFinishedRegistrationClientsForSendEmail();
        foreach ($clients as $client) {
            $mailer->sendClientNotFinishedRegistrationEmail($client);
        }
        $output->write(' - success', true);

        $output->write('Start sending emails for Clients which not approved portfolio');
        $clients = $em->getRepository('WealthbotUserBundle:User')->findNotApprovedPortfolioClientsForSendEmail();
        foreach ($clients as $client) {
            $mailer->sendClientNotApprovedPortfolioEmail($client);
        }
        $output->write(' - success', true);

        $output->write('Start sending emails for Clients which not completed all applications');
        $clients = $em->getRepository('WealthbotUserBundle:User')->findNotCompleteAllApplicationsClientForSendEmail();
        foreach ($clients as $client) {
            $mailer->sendClientNotCompleteAllApplicationsEmail($client);
        }
        $output->write(' - success', true);
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
