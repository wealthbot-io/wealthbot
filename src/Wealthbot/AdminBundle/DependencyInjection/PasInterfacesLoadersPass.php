<?php

namespace Wealthbot\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PasInterfacesLoadersPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('wealthbot_admin.pas_interface.information')) {
            return;
        }

        $definition = $container->getDefinition('wealthbot_admin.pas_interface.information');

        $taggedServices = $container->findTaggedServiceIds('wealthbot_admin.pas_interface_loader');

        $services = [];
        foreach ($taggedServices as $id => $attributes) {
            $services[] = new Reference($id);
        }

        $definition->replaceArgument(0, $services);
    }
}
