<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Workflow;

class SetWorkflowMessageCodeCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:client:workflow-message-code')
            ->setDescription('Fix old workflow items. Set message_code by message.')
            ->setHelp(
                <<<EOT
The <info>rx:client:workflow-message-code</info> command fix old workflow items. Set message_code by message.:

  <info>php app/console rx:client:workflow-message-code</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\Workflow');
        $workflow = $repository->findAll();

        $count = 0;
        foreach ($workflow as $workflowItem) {
            $type = $workflowItem->getType();

            if (Workflow::TYPE_PAPERWORK === $type) {
                $messageCodes = array_flip(Workflow::getPaperworkMessageChoices());
            } else {
                $messageCodes = array_flip(Workflow::getAlertMessageChoices());
            }

            $workflowItem->setMessageCode($messageCodes[$workflowItem->getDbMessage()]);
            $em->persist($workflowItem);
            ++$count;
        }

        $em->flush();

        $output->writeln(sprintf('Updated %d items.', $count));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
