<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.09.13
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */

namespace App\Exception;

class AdvisorHasNoExistingModel extends \Exception
{
    public function __construct($message = 'Advisor does not have existing model.', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
