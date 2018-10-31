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
use Symfony\Component\DependencyInjection\Definition;

/**
 * AddTranslationsPass adds translations from the sanitycheck library to the Symfony translator.
 */
class AddTranslationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $translatorDefinition = $container->getDefinition('translator.default');

        $index = 3;
        if (false === $this->hasResourceFilesOption($translatorDefinition, $index)) {
            $index = 4;
            if (false === $this->hasResourceFilesOption($translatorDefinition, $index)) {
                return;
            }
        }
        $options = $translatorDefinition->getArgument($index);
        $configuration = new SanityCheckConfiguration();
        $options['resource_files'] = array_merge_recursive($options['resource_files'], $configuration->getTranslationResources());
        $translatorDefinition->replaceArgument($index, $options);
    }

    /**
     * Checks if $translatorDefinition has an options array at the expected $index. The index is different between
     * Symfony versions, so we need to be able to poke around.
     *
     * @param Definition $translatorDefinition
     * @param int $index
     *
     * @return bool
     */
    private function hasResourceFilesOption(Definition $translatorDefinition, int $index): bool
    {
        $options = $translatorDefinition->getArgument($index);

        return true === \is_array($options) && true === \array_key_exists('resource_files', $options);
    }
}
