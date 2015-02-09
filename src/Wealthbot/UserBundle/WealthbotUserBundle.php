<?php

namespace Wealthbot\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WealthbotUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
