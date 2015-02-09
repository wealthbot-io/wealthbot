<?php

namespace Wealthbot\AdminBundle;

use Wealthbot\AdminBundle\DependencyInjection\PasInterfacesLoadersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WealthbotAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PasInterfacesLoadersPass());
    }
}
