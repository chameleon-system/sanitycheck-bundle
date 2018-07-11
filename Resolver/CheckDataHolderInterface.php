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

/**
 * CheckDataHolderInterface defines a repository for check data. Its intention is to be
 * solely used by implementations of ChameleonSystem\SanityCheck\Resolver\CheckResolverInterface.
 */
interface CheckDataHolderInterface
{
    /**
     * Returns all checks for a given bundle name.
     *
     * @param string $bundleName the bundle name for which checks should be returned
     *
     * @return array[] the checks defined for the given bundle
     *
     * @throws CheckNotFoundException if there are no checks for this bundle
     */
    public function getChecksForBundle($bundleName);

    /**
     * Returns all defined checks in a flat list.
     *
     * @return array[] a list of all checks defined in the system
     */
    public function getAllChecks();

    /**
     * Returns all defined checks in an array of arrays (bundleAlias => check-list).
     *
     * @return array an array of arrays which holds the checks per bundle
     */
    public function getBundleCheckData();

    /**
     * Sets the check data.
     *
     * @param array $checkData the check data to set (no surprise here)
     */
    public function setBundleCheckData(array $checkData);
}
