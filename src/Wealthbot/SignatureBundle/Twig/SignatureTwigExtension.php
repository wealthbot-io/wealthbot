<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.10.13
 * Time: 17:23
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\SignatureBundle\Twig;


use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\ClientBundle\Model\ClientAccount;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\SignatureBundle\Manager\DocumentSignatureManager;

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
        return array(
            new \Twig_SimpleFunction('is_signature_completed', array($this, 'isSignatureCompleted')),
            new \Twig_SimpleFunction('is_owner_signature_completed', array($this, 'isOwnerSignatureCompleted')),
            new \Twig_SimpleFunction('is_application_signed', array($this, 'isApplicationSigned')),
            new \Twig_SimpleFunction('is_owner_sign_application', array($this, 'isOwnerSignApplication')),
            new \Twig_SimpleFunction('document_signature_source', array($this, 'getDocumentSignatureSource')),
            new \Twig_SimpleFunction('document_signature_activity', array($this, 'getDocumentSignatureActivity'))
        );
    }

    /**
     * Is document signature completed
     *
     * @param DocumentSignature|int $signature
     * @return bool
     */
    public function isSignatureCompleted($signature)
    {
        return $this->manager->isSignatureCompleted($signature);
    }

    /**
     * Is client account owner has complete signature
     *
     * @param AccountOwnerInterface $accountOwner
     * @param DocumentSignature $signature
     * @return bool
     */
    public function isOwnerSignatureCompleted(AccountOwnerInterface $accountOwner, DocumentSignature $signature)
    {
        return $this->manager->isOwnerSignatureCompleted($accountOwner, $signature);
    }

    /**
     * Is all client account in application signed
     *
     * @param ClientAccount|int $account
     * @return bool
     */
    public function isApplicationSigned($account)
    {
        return $this->manager->isApplicationSigned($account);
    }

    /**
     * Is account owner sign all document signatures for application
     *
     * @param AccountOwnerInterface $accountOwner
     * @param ClientAccount $account
     * @return bool
     */
    public function isOwnerSignApplication(AccountOwnerInterface $accountOwner, ClientAccount $account)
    {
        return $this->manager->isOwnerSignApplication($accountOwner, $account);
    }

    /**
     * Get source object of document signature
     *
     * @param DocumentSignature $signature
     * @return \Wealthbot\SignatureBundle\Model\SignableInterface
     */
    public function getDocumentSignatureSource(DocumentSignature $signature)
    {
        return $this->manager->getSourceObject($signature);
    }

    /**
     * Get document signature activity
     *
     * @param DocumentSignature $signature
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