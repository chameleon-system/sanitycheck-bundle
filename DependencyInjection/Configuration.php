<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\DependencyInjection;

use ChameleonSystem\SanityCheck\Outcome\CheckOutcome;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('chameleon_system_sanity_check');
        $root = $treeBuilder->getRootNode();

        $root
            ->canBeDisabled()
            ->children()
                ->append($this->getOutputConfiguration())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function getOutputConfiguration()
    {
        $subTreeBuilder = new TreeBuilder('output');
        $subTree = $subTreeBuilder->getRootNode();

        $subTree
            ->children()
                ->append($this->getLogOutputConfiguration())
                ->append($this->getMailerConfiguration())
            ->end();

        return $subTree;
    }

    /**
     * @return NodeDefinition
     */
    private function getLogOutputConfiguration()
    {
        $subTreeBuilder = new TreeBuilder('log');
        $subTree = $subTreeBuilder->getRootNode();

        $subTree
            ->children()
                ->scalarNode('logger')
                    ->defaultNull()
                ->end()
            ->end();

        return $subTree;
    }

    /**
     * @return NodeDefinition
     */
    private function getMailerConfiguration()
    {
        $subTreeBuilder = new TreeBuilder('mail');
        $subTree = $subTreeBuilder->getRootNode();

        $subTree
            ->canBeEnabled()
            ->children()
                ->scalarNode('from')
                    ->defaultValue('root@localhost')
                ->end()
                ->scalarNode('to')
                    ->isRequired()
                ->end()
                ->enumNode('implementation')
                    ->values(array('phpmailer', ''))
                    ->isRequired()
                ->end()
                ->scalarNode('service')
                    ->defaultNull()
                ->end()
                ->enumNode('level')
                    ->values(CheckOutcome::$LEVELS)
                    ->defaultValue(CheckOutcome::WARNING)
                ->end()
            ->end()
        ;

        return $subTree;
    }

    private function getTranslationConfiguration()
    {
        $subTreeBuilder = new TreeBuilder('translation');
        $subTree = $subTreeBuilder->getRootNode();

        $subTree
            ->children()
                ->scalarNode('class')
                    ->defaultValue('')
                ->end()
            ->end()
        ;
    }
}
