<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\UserBundle\Mailer;

use Wealthbot\UserBundle\Entity\User;

/**
 *  @author
 */
interface MailerInterface
{
    /**
     * Send an email to a client with ria adv copy attachment.
     *
     * @param \Wealthbot\UserBundle\Entity\User $client
     *
     * @return mixed
     */
    public function sendAdvCopyEmailMessage(User $client);
}
