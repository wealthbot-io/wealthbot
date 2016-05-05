<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wealthbot\AdminBundle\Mailer;

use Wealthbot\UserBundle\Entity\User;

/**
 *  @author
 */
interface MailerInterface
{
    /**
     * Send an email to a $toEmail with message about ria activated.
     *
     * @param $toEmail
     * @param \Wealthbot\UserBundle\Entity\User $ria
     *
     * @return mixed
     */
    public function sendRiaActivatedEmailMessage($toEmail, User $ria);
}
