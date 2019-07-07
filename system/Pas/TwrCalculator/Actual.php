<?php

namespace Pas\TwrCalculator;

use Pas\TwrCalculator\Actual\IRule;

class Actual
{
    public function rule(IRule $rule)
    {
        return $rule->calculate();
    }
}