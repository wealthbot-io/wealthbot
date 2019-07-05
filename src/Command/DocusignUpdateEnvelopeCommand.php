<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.10.13
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\DocumentSignature;

class DocusignUpdateEnvelopeCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('rx:docusign:update-envelopes')
            ->setDescription('Update docusign envelopes statuses.')
            ->setHelp(
                <<<EOT
The <info>rx:docusign:update-envelopes</info> command updates docusign envelopes statuses:

  <info>php app/console rx:docusign:update-envelopes</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $electronicSignature = $this->getContainer()->get('wealthbot_docusign.electronic_signature_service');
        $repository = $em->getRepository('App\Entity\DocumentSignature');

        $signatures = $repository->getSignaturesToUpdate();

        /** @var DocumentSignature $signature */
        foreach ($signatures as $signature) {
            $signatureEnvelopeId = $signature->getDocusignEnvelopeId();
            if (!$signatureEnvelopeId) {
                continue;
            }

            $output->writeln(sprintf('Update status for envelope: %s', $signatureEnvelopeId));
            $electronicSignature->updateDocumentSignaturesStatusByEnvelopeId($signatureEnvelopeId, [$signature]);
            $output->writeln('ok.');
        }

        $output->writeln('Completed.');
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }
}
