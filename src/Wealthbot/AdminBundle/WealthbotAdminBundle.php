<?php

namespace Wealthbot\AdminBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wealthbot\AdminBundle\DependencyInjection\PasInterfacesLoadersPass;

class WealthbotAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PasInterfacesLoadersPass());
    }
}
