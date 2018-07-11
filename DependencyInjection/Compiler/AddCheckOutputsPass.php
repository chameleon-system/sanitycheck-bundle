<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * AddCheckOutputsPass adds all services tagged with 'chameleon_system.sanity_check.output' to the list of outputs.
 */
class AddCheckOutputsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('chameleon_system_sanity_check.output_resolver')) {
            return;
        }
        $definition = $container->findDefinition('chameleon_system_sanity_check.output_resolver');

        $taggedServices = $container->findTaggedServiceIds(
            'chameleon_system.sanity_check.output'
        );

        foreach ($taggedServices as $serviceId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addOutput',
                    array($attributes['alias'], new Reference($serviceId))
                );
            }
        }
    }
}
