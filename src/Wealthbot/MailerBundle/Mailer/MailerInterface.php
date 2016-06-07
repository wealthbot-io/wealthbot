<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\MailerBundle\Mailer;

use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\UserBundle\Entity\User;

/**
 *  @author
 */
interface MailerInterface
{
    //------------------ADMIN EMAILS---------------------

    public function sendAdminsRiaActivatedEmail(User $ria);

    //------------------RIA EMAILS---------------------

    public function sendRiaNotFinishedRegistrationEmail(User $ria);

    public function sendRiaChangePasswordEmail(User $ria);

    public function sendRiaUserResetPasswordEmail(User $ria, User $riaUser, $newPassword);

    public function sendRiaUserCreateEmail(User $riaUser, $password);

    public function sendRiaClientSuggestedPortfolioEmail(User $client);

    public function sendRiaClientClosedAccountsEmail(User $client, $accounts);

    public function sendRiaActivatedEmail(User $ria);

    //-----------------CLIENT EMAILS---------------------

    public function sendClientNotFinishedRegistrationEmail(User $client);

    public function sendClientNotApprovedPortfolioEmail(User $client);

    public function sendClientNotCompleteAllApplicationsEmail(User $client);

    public function sendClientRolloverInstruction401Email(ClientAccount $account, $rolloverMessage);

    public function sendClientUserCreateEmail(User $clientUser, $password);

    public function sendClientResetSelfPasswordEmail(User $client, $newPassword);

    public function sendClientResetPasswordEmail(User $client);

    public function sendClientUpdatedDocumentsEmail(User $client, $documentType);

    public function sendClientInviteProspectEmail(User $ria, $clientEmail, $group = null);

    public function sendClientInviteInternalProspectEmail(User $ria, User $client, $tmpPassword);

    public function sendClientAdvCopyEmailMessage(User $client);

    public function sendClientPortfolioIsSubmittedEmail(User $client);

    public function sendClientRiaUploadedDocument(User $clients);

    //----------------DOCUSIGN EMAILS---------------------

    public function sendDocusignJointAccountOwnerEmail(AccountOwnerInterface $jointOwner, User $ria);

    public function sendCustodianWorkflowDocuments(User $ria, Workflow $workflow);
}
