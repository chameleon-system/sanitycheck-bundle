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

use ChameleonSystem\SanityCheck\Exception\CheckNotFoundException;
use PHPUnit\Framework\TestCase;

class CheckDataHolderTest extends TestCase
{
    /**
     * @var CheckDataHolder $checkDataHolder
     */
    protected $checkDataHolder;

    protected $data;
    protected $dataFlat;

    public function testGetChecksForBundleOk()
    {
        $this->setSomeData();

        $checks = $this->checkDataHolder->getChecksForBundle('chameleon_system_such_bundle');

        $this->assertEquals($this->data['chameleon_system_such_bundle'], $checks);
    }

    public function testGetChecksForBundleNotFound()
    {
        $this->setSomeData();
        try {
            $checks = $this->checkDataHolder->getChecksForBundle('chameleon_system_very_bundle');
            $this->assertTrue(false);
        } catch (CheckNotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetAllChecksOk()
    {
        $this->setSomeData();

        $checks = $this->checkDataHolder->getAllChecks();

        $this->assertEquals($this->dataFlat, $checks);
    }

    public function testGetAllChecksEmpty()
    {
        $checks = $this->checkDataHolder->getAllChecks();

        $this->assertEmpty($checks);
    }

    public function setSomeData()
    {
        $this->data = array(
            'chameleon_system_sanitycheck' => array('chameleon_system_sanitycheck.check1'),
            'chameleon_system_such_bundle' => array(
                'chameleon_system_sanitycheck.check2',
                'chameleon_system_sanitycheck.check3',
            ),
        );

        $this->dataFlat = array(
            'chameleon_system_sanitycheck.check1',
            'chameleon_system_sanitycheck.check2',
            'chameleon_system_sanitycheck.check3',
        );

        $this->checkDataHolder->setBundleCheckData($this->data);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkDataHolder = new CheckDataHolder();
    }
}
