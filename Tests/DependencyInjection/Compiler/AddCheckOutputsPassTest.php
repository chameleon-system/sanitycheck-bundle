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

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddCheckOutputsPassTest extends TestCase
{
    /**
     * @var AddCheckOutputsPass $addCheckOutputsPass
     */
    protected $addCheckOutputsPass;

    /**
     * @var ContainerBuilder $container
     */
    protected $container;

    /**
     * @var Definition $outputResolverDefinition ;
     */
    protected $outputResolverDefinition;

    public function testAddNoOutputs()
    {
        $outputs = array();

        $results = array();

        $this->doTests($outputs, $results);
    }

    public function testAddOneOutput()
    {
        $outputs = array(
            'chameleon_system_test.output1' => array(array('alias' => 'output1')),
        );

        $results = array(
            'output1' => 'chameleon_system_test.output1',
        );

        $this->doTests($outputs, $results);
    }

    public function testAddMultipleOutputs()
    {
        $outputs = array(
            'chameleon_system_test.output1' => array(array('alias' => 'output1')),
            'chameleon_system_test.output2' => array(array('alias' => 'output2')),
        );

        $results = array(
            'output1' => 'chameleon_system_test.output1',
            'output2' => 'chameleon_system_test.output2',
        );

        $this->doTests($outputs, $results);
    }

    private function doTests($outputs, $results)
    {
        $this->container->findTaggedServiceIds('chameleon_system.sanity_check.output')->willReturn($outputs);
        if (0 === count($results)) {
            $this->outputResolverDefinition->addMethodCall('addOutput', Argument::any())->shouldNotBeCalled();
        } else {
            foreach ($results as $alias => $serviceId) {
                $this->outputResolverDefinition->addMethodCall('addOutput', array($alias, new Reference($serviceId)))->shouldBeCalled();
            }
        }

        $this->addCheckOutputsPass->process($this->container->reveal());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->addCheckOutputsPass = new AddCheckOutputsPass();
        $this->container = $this->prophesize(ContainerBuilder::class);
        $this->outputResolverDefinition = $this->prophesize(Definition::class);
        $this->container->hasDefinition('chameleon_system_sanity_check.output_resolver')->willReturn(true);
        $this->container->findDefinition('chameleon_system_sanity_check.output_resolver')->willReturn(
            $this->outputResolverDefinition->reveal()
        );
    }
}
