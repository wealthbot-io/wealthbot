<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.12
 * Time: 12:10
 * To change this template use File | Settings | File Templates.
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class FeeTier extends Constraint
{
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'validator.rx_fee_tier';
    }
}
