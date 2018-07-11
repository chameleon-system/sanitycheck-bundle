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

use ChameleonSystem\SanityCheck\Configuration\SanityCheckConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * AddCheckOutputsPass adds all services tagged with 'chameleon_system.sanity_check.output' to the list of outputs.
 */
class AddTranslationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $translatorDefinition = $container->getDefinition('translator.default');

        $configuration = new SanityCheckConfiguration();
        $options = $translatorDefinition->getArgument(3);
        $options['resource_files'] = array_merge_recursive($options['resource_files'], $configuration->getTranslationResources());
        $translatorDefinition->replaceArgument(3, $options);
    }
}
