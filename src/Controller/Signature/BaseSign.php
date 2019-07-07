<?php

/**
 * inject into container.
 */

namespace App\Controller\Signature;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseSign extends Controller
{
    protected $api;

    protected $signatureManager;

    protected $electronicSignature;

    protected $em;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->api = $this->get('wealthbot_docusign.api_client');
        $this->signatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $this->electronicSignature = $this->get('wealthbot_docusign.electronic_signature_service');
        $this->em = $this->get('doctrine.orm.entity_manager');
    }
}
