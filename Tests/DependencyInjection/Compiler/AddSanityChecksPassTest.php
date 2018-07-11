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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddSanityChecksPassTest extends TestCase
{
    /**
     * @var AddSanityChecksPass $addSanityChecksPass
     */
    protected $addSanityChecksPass;

    /**
     * @var ContainerBuilder $container
     */
    protected $container;

    /**
     * @var Definition $definition ;
     */
    protected $definition;

    public function testAddOneCheck()
    {
        $checks = array(
            'chameleon_system_test.check1' => array(array()),
        );

        $results = array(
            'chameleon_system_test' => array(array('chameleon_system_test.check1', null)),
        );

        $this->doTests($checks, $results);
    }

    public function testAddOneCheckWithTranslationKey()
    {
        $checks = array(
            'chameleon_system_test.check1' => array(array('translation_key' => 'label.test')),
        );

        $results = array(
            'chameleon_system_test' => array(array('chameleon_system_test.check1', 'label.test')),
        );

        $this->doTests($checks, $results);
    }

    public function testAddMultipleChecks()
    {
        $checks = array(
            'chameleon_system_test.check1' => array(array()),
            'chameleon_system_test.check2' => array(array('translation_key' => 'label.test')),
            'chameleon_system_test.check3' => array(array()),
        );

        $results = array(
            'chameleon_system_test' => array(
                array('chameleon_system_test.check1', null),
                array('chameleon_system_test.check2', 'label.test'),
                array('chameleon_system_test.check3', null),
            ),
        );

        $this->doTests($checks, $results);
    }

    public function testAddMultipleChecksForMultipleBundles()
    {
        $checks = array(
            'chameleon_system_test1.check1' => array(array()),
            'chameleon_system_test1.check2' => array(array()),
            'chameleon_system_test2.check1' => array(array()),
            'chameleon_system_test2.check2' => array(array()),
            'chameleon_system_test2.check3' => array(array()),
            'chameleon_system_test3.check1' => array(array()),
        );

        $results = array(
            'chameleon_system_test1' => array(
                array('chameleon_system_test1.check1', null),
                array('chameleon_system_test1.check2', null),
            ),
            'chameleon_system_test2' => array(
                array('chameleon_system_test2.check1', null),
                array('chameleon_system_test2.check2', null),
                array('chameleon_system_test2.check3', null),
            ),
            'chameleon_system_test3' => array(
                array('chameleon_system_test3.check1', null),
            ),
        );

        $this->doTests($checks, $results);
    }

    public function testNoChecks()
    {
        $checks = array();

        $results = array();

        $this->container->findTaggedServiceIds('chameleon_system.sanity_check.check')->willReturn($checks);
        $this->definition->addMethodCall('setBundleCheckData', array($results))->shouldNotBeCalled();

        $this->addSanityChecksPass->process($this->container->reveal());
    }

    public function testNonPrefixedCheck()
    {
        $checks = array(
            'chameleon_system_test.check1' => array(array()),
            'check2' => array(array()),
            'chameleon_system_test.check3' => array(array('translation_key' => 'label.test')),
        );

        $results = array(
            'chameleon_system_test' => array(
                array('chameleon_system_test.check1', null),
                array('check2', null),
                array('chameleon_system_test.check3', 'label.test'),
            ),
        );

        $this->container->findTaggedServiceIds('chameleon_system.sanity_check.check')->willReturn($checks);
        $this->definition->addMethodCall('setBundleCheckData', array($results))->shouldNotBeCalled();
        try {
            $this->addSanityChecksPass->process($this->container->reveal());
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    private function doTests($checks, $results)
    {
        $this->container->findTaggedServiceIds('chameleon_system.sanity_check.check')->willReturn($checks);
        $this->definition->addMethodCall('setBundleCheckData', array($results))->shouldBeCalled();

        $this->addSanityChecksPass->process($this->container->reveal());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->addSanityChecksPass = new AddSanityChecksPass();
        $this->container = $this->prophesize(ContainerBuilder::class);
        $this->definition = $this->prophesize(Definition::class);
        $this->container->findDefinition('chameleon_system_sanity_check.check_data_holder')->willReturn(
            $this->definition->reveal()
        );
    }
}
