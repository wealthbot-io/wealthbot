<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.03.13
 * Time: 15:28
 * To change this template use File | Settings | File Templates.
 */

namespace App\Exception;

class DataAlreadyExistsException extends \Exception
{
    public function __construct($message = 'Data already exists.', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
