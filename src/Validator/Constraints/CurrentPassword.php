<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CurrentPassword extends Constraint
{
    public $message = 'This value should be the user current password.';

    public function validatedBy()
    {
        return 'wealthbot_ria.validator.user_password';
    }
}
