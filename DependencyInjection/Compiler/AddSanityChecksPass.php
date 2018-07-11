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

/**
 * AddSanityChecksPass finds all services tagged with 'chameleon_system.sanity_check.check' and adds them
 * to the check repository (can later be found through CheckResolverInterface by service id).
 */
class AddSanityChecksPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds(
            'chameleon_system.sanity_check.check'
        );

        $checkData = array();
        foreach ($taggedServices as $serviceId => $attributes) {
            $bundleName = $this->getBundleAliasFromServiceId($serviceId);

            if (!array_key_exists($bundleName, $checkData)) {
                $checkData[$bundleName] = array();
            }
            $translationKey = null;
            foreach ($attributes as $tag) {
                foreach ($tag as $key => $value) {
                    if ('translation_key' === $key) {
                        $translationKey = $value;
                    }
                }
            }
            $checkData[$bundleName][] = array($serviceId, $translationKey);
        }
        if (empty($checkData)) {
            return;
        }
        $container->findDefinition('chameleon_system_sanity_check.check_data_holder')->addMethodCall(
            'setBundleCheckData',
            array($checkData)
        );
    }

    private function getBundleAliasFromServiceId($serviceId)
    {
        $pos = strpos($serviceId, '.');
        if (false === $pos) {
            throw new \InvalidArgumentException(
                'Invalid check name. Checks need to be prefixed by their bundle alias.'
            );
        }

        return substr($serviceId, 0, $pos);
    }
}
