<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\Resolver;

use ChameleonSystem\SanityCheck\Check\CheckInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ChameleonSystem\SanityCheck\Exception\CheckNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SymfonyContainerCheckResolverTest extends TestCase
{
    /**
     * @var SymfonyContainerCheckResolver $checkResolver
     */
    protected $checkResolver;
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var CheckDataHolderInterface $checkDataHolder
     */
    protected $checkDataHolder;

    /**
     * @var CheckInterface $check1
     */
    protected $check1;
    /**
     * @var CheckInterface $check2
     */
    protected $check2;
    /**
     * @var CheckInterface $check3
     */
    protected $check3;

    public function testFindSingleOk()
    {
        $revealedCheck = $this->check1->reveal();
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck);
        $checks = $this->checkResolver->findChecksForName('chameleon_system_sanitycheck.test.check1');

        $this->assertCount(1, $checks);
        $this->assertEquals($revealedCheck, $checks[0]);
    }

    public function testFindSingleNotFound()
    {
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willThrow(
            'ChameleonSystem\SanityCheck\Exception\CheckNotFoundException'
        );
        $this->expectException(CheckNotFoundException::class);
        $this->checkResolver->findChecksForName('chameleon_system_sanitycheck.test.check1');
    }

    public function testFindBundleOk()
    {
        $revealedCheck = $this->check1->reveal();
        $this->checkDataHolder->getChecksForBundle('chameleon_system_sanitycheck')->willReturn(
            array(array('chameleon_system_sanitycheck.test.check1', 'label.test'))
        );
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck);
        $checks = $this->checkResolver->findChecksForName('chameleon_system_sanitycheck');

        $this->assertCount(1, $checks);
        $this->assertEquals($revealedCheck, $checks[0]);
    }

    public function testFindBundleWithAtOk()
    {
        $revealedCheck = $this->check1->reveal();
        $this->checkDataHolder->getChecksForBundle('chameleon_system_sanitycheck')->willReturn(
            array(array('chameleon_system_sanitycheck.test.check1', null))
        );
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck);
        $checks = $this->checkResolver->findChecksForName('@ChameleonSystemSanitycheck');

        $this->assertCount(1, $checks);
        $this->assertEquals($revealedCheck, $checks[0]);
    }

    public function testFindBundleNotFound()
    {
        $this->checkDataHolder->getChecksForBundle('chameleon_system_sanitycheck')->willThrow(
            'ChameleonSystem\SanityCheck\Exception\CheckNotFoundException'
        );
        $this->expectException(CheckNotFoundException::class);
        $this->checkResolver->findChecksForName('chameleon_system_sanitycheck');
    }

    public function testFindBundleNoTestsFound()
    {
        $this->checkDataHolder->getChecksForBundle('chameleon_system_sanitycheck')->willReturn(array());
        $checks = $this->checkResolver->findChecksForName('chameleon_system_sanitycheck');

        $this->assertCount(0, $checks);
    }

    public function testFindMultipleOk()
    {
        $revealedCheck1 = $this->check1->reveal();
        $revealedCheck2 = $this->check2->reveal();
        $revealedCheck3 = $this->check3->reveal();
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck1);
        $this->container->get('chameleon_system_sanitycheck.test.check2')->willReturn($revealedCheck2);
        $this->container->get('chameleon_system_sanitycheck.test.check3')->willReturn($revealedCheck3);

        $checks = $this->checkResolver->findChecksForNameList(
            array(
                'chameleon_system_sanitycheck.test.check1',
                'chameleon_system_sanitycheck.test.check2',
                'chameleon_system_sanitycheck.test.check3',
            )
        );

        $this->assertCount(3, $checks);
        $this->assertEquals($revealedCheck1, $checks[0]);
        $this->assertEquals($revealedCheck2, $checks[1]);
        $this->assertEquals($revealedCheck3, $checks[2]);
    }

    public function testFindMultipleWithBundle()
    {
        $revealedCheck1 = $this->check1->reveal();
        $revealedCheck2 = $this->check2->reveal();
        $revealedCheck3 = $this->check3->reveal();
        $this->checkDataHolder->getChecksForBundle('chameleon_system_sanitycheck')->willReturn(
            array(array('chameleon_system_sanitycheck.test.check2', null))
        );
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck1);
        $this->container->get('chameleon_system_sanitycheck.test.check2')->willReturn($revealedCheck2);
        $this->container->get('chameleon_system_sanitycheck.test.check3')->willReturn($revealedCheck3);

        $checks = $this->checkResolver->findChecksForNameList(
            array(
                'chameleon_system_sanitycheck.test.check1',
                'chameleon_system_sanitycheck',
                'chameleon_system_sanitycheck.test.check3',
            )
        );

        $this->assertCount(3, $checks);
        $this->assertEquals($revealedCheck1, $checks[0]);
        $this->assertEquals($revealedCheck2, $checks[1]);
        $this->assertEquals($revealedCheck3, $checks[2]);
    }

    public function testFindMultipleSomeNotFound()
    {
        $revealedCheck1 = $this->check1->reveal();
        $revealedCheck3 = $this->check3->reveal();
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck1);
        $this->container->get('chameleon_system_sanitycheck.test.check2')->willThrow('\InvalidArgumentException');
        $this->container->get('chameleon_system_sanitycheck.test.check3')->willReturn($revealedCheck3);

        $this->expectException(CheckNotFoundException::class);
        $checks = $this->checkResolver->findChecksForNameList(
            array(
                'chameleon_system_sanitycheck.test.check1',
                'chameleon_system_sanitycheck.test.check2',
                'chameleon_system_sanitycheck.test.check3',
            )
        );
    }

    public function testFindMultipleNoneFound()
    {
        $this->container->get('chameleon_system_sanitycheck.test.such_check_very_absence')->willThrow('\InvalidArgumentException');
        $this->container->get('chameleon_system_sanitycheck.test.many_check_no_find')->willThrow('\InvalidArgumentException');
        $this->expectException(CheckNotFoundException::class);
        $this->checkResolver->findChecksForNameList(
            array(
                'chameleon_system_sanitycheck.test.such_check_very_absence',
                'chameleon_system_sanitycheck.test.many_check_no_find',
            )
        );
    }

    public function testFindAll()
    {
        $revealedCheck1 = $this->check1->reveal();
        $revealedCheck2 = $this->check2->reveal();
        $revealedCheck3 = $this->check3->reveal();
        $this->checkDataHolder->getAllChecks()->willReturn(
            array(
                array('chameleon_system_sanitycheck.test.check1', null),
                array('chameleon_system_sanitycheck.test.check2', null),
                array('chameleon_system_sanitycheck.test.check3', null),
            )
        );
        $this->container->get('chameleon_system_sanitycheck.test.check1')->willReturn($revealedCheck1);
        $this->container->get('chameleon_system_sanitycheck.test.check2')->willReturn($revealedCheck2);
        $this->container->get('chameleon_system_sanitycheck.test.check3')->willReturn($revealedCheck3);

        $checks = $this->checkResolver->findAllChecks();

        $this->assertCount(3, $checks);
        $this->assertEquals($revealedCheck1, $checks[0]);
        $this->assertEquals($revealedCheck2, $checks[1]);
        $this->assertEquals($revealedCheck3, $checks[2]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->prophesize(ContainerBuilder::class);
        $this->checkDataHolder = $this->prophesize(
            CheckDataHolderInterface::class
        );
        $this->checkResolver = new SymfonyContainerCheckResolver($this->container->reveal(), $this->checkDataHolder->reveal());
        $this->check1 = $this->prophesize(CheckInterface::class);
        $this->check2 = $this->prophesize(CheckInterface::class);
        $this->check3 = $this->prophesize(CheckInterface::class);
    }
}
