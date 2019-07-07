<?php

namespace System\Pas\TwrCalculator;

use System\Pas\TwrCalculator\Actual\IRule;

class Actual
{
    public function rule(IRule $rule)
    {
        return $rule->calculate();
    }
}