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

class CheckDataHolder implements CheckDataHolderInterface
{
    private $checkData = array();

    /**
     * {@inheritdoc}
     */
    public function getChecksForBundle($bundleName)
    {
        if (!array_key_exists($bundleName, $this->checkData)) {
            throw new CheckNotFoundException('Bundle not found: '.$bundleName, 0);
        }

        return $this->checkData[$bundleName];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChecks()
    {
        $retValue = array();
        if (empty($this->checkData)) {
            return $retValue;
        }
        foreach ($this->checkData as $bundleCheckData) {
            foreach ($bundleCheckData as $check) {
                $retValue[] = $check;
            }
        }

        return $retValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleCheckData()
    {
        return $this->checkData;
    }

    /**
     * {@inheritdoc}
     */
    public function setBundleCheckData(array $checkData)
    {
        $this->checkData = $checkData;
    }
}
