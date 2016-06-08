<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\RiaBundle\Mailer;

use Wealthbot\UserBundle\Entity\User;

/**
 *  @author
 */
interface MailerInterface
{
    /**
     * Send an email to a user to confirm the account creation.
     *
     * @param User $user
     */
    public function sendConfirmationEmailMessage(User $user);
}
