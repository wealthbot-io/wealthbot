<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\ClientBundle\Mailer;

use Wealthbot\UserBundle\Entity\User;

/**
 *  @author
 */
interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param \Wealthbot\UserBundle\Entity\User $client
     */
    public function sendSuggestedPortfolioEmailMessage(User $client);

    /**
     * Send email to ria when client close account.
     *
     * @param User $client
     * @param $systemAccounts
     * @param array $closeMessages
     *
     * @return mixed
     */
    public function sendCloseAccountsMessage(User $client, $systemAccounts, array $closeMessages);

    /**
     * Send welcome message to new client.
     *
     * @param User   $client
     * @param string $password
     *
     * @return mixed
     */
    public function sendWelcomeMessage(User $client, $password);
}
