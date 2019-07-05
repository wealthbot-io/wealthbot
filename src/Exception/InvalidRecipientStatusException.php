<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.10.13
 * Time: 14:52
 * To change this template use File | Settings | File Templates.
 */

namespace App\Exception;

class InvalidRecipientStatusException extends \Exception
{
    public function __construct($message = 'Invalid recipient status.', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
