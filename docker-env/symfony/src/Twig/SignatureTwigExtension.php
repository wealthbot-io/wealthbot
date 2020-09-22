<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.10.13
 * Time: 17:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Twig;

use App\Model\AccountOwnerInterface;
use App\Model\ClientAccount;
use App\Entity\DocumentSignature;
use App\Manager\DocumentSignatureManager;

class SignatureTwigExtension extends \Twig_Extension
{
    /** @var DocumentSignatureManager */
    private $manager;

    public function __construct(DocumentSignatureManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('is_signature_completed', [$this, 'isSignatureCompleted']),
            new \Twig\TwigFunction('is_owner_signature_completed', [$this, 'isOwnerSignatureCompleted']),
            new \Twig\TwigFunction('is_application_signed', [$this, 'isApplicationSigned']),
            new \Twig\TwigFunction('is_owner_sign_application', [$this, 'isOwnerSignApplication']),
            new \Twig\TwigFunction('document_signature_source', [$this, 'getDocumentSignatureSource']),
            new \Twig\TwigFunction('document_signature_activity', [$this, 'getDocumentSignatureActivity']),
        ];
    }

    /**
     * Is document signature completed.
     *
     * @param DocumentSignature|int $signature
     *
     * @return bool
     */
    public function isSignatureCompleted($signature)
    {
        return $this->manager->isSignatureCompleted($signature);
    }

    /**
     * Is client account owner has complete signature.
     *
     * @param AccountOwnerInterface $accountOwner
     * @param DocumentSignature     $signature
     *
     * @return bool
     */
    public function isOwnerSignatureCompleted(AccountOwnerInterface $accountOwner, DocumentSignature $signature)
    {
        return $this->manager->isOwnerSignatureCompleted($accountOwner, $signature);
    }

    /**
     * Is all client account in application signed.
     *
     * @param ClientAccount|int $account
     *
     * @return bool
     */
    public function isApplicationSigned($account)
    {
        return $this->manager->isApplicationSigned($account);
    }

    /**
     * Is account owner sign all document signatures for application.
     *
     * @param AccountOwnerInterface $accountOwner
     * @param ClientAccount         $account
     *
     * @return bool
     */
    public function isOwnerSignApplication(AccountOwnerInterface $accountOwner, ClientAccount $account)
    {
        return $this->manager->isOwnerSignApplication($accountOwner, $account);
    }

    /**
     * Get source object of document signature.
     *
     * @param DocumentSignature $signature
     *
     * @return \App\Model\SignableInterface
     */
    public function getDocumentSignatureSource(DocumentSignature $signature)
    {
        return $this->manager->getSourceObject($signature);
    }

    /**
     * Get document signature activity.
     *
     * @param DocumentSignature $signature
     *
     * @return string
     */
    public function getDocumentSignatureActivity(DocumentSignature $signature)
    {
        return $this->manager->getActivity($signature);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'rx_signature_twig_extension';
    }
}
