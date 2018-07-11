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
use ChameleonSystem\SanityCheck\Exception\CheckNotFoundException;
use ChameleonSystem\SanityCheck\Resolver\CheckResolverInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SymfonyContainerCheckResolver implements CheckResolverInterface
{
    private $container;
    private $checkDataHolder;

    /**
     * @param ContainerInterface       $container
     * @param CheckDataHolderInterface $checkDataHolder
     */
    public function __construct(ContainerInterface $container, CheckDataHolderInterface $checkDataHolder)
    {
        $this->container = $container;
        $this->checkDataHolder = $checkDataHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function findChecksForName($name)
    {
        $retValue = array();
        $firstCharIsBundleMarker = '@' === substr($name, 0, 1);
        $isPrefixedWithDot = false !== strpos($name, '.');
        if ($firstCharIsBundleMarker || !$isPrefixedWithDot) {
            if ($firstCharIsBundleMarker) {
                $bundleAlias = Container::underscore(substr($name, 1));
            } else {
                $bundleAlias = $name;
            }
            $checks = $this->checkDataHolder->getChecksForBundle($bundleAlias);
            foreach ($checks as $checkData) {
                $retValue[] = $this->lookupCheckByServiceId($checkData[0]);
            }
        } else {
            $retValue[] = $this->lookupCheckByServiceId($name);
        }

        return $retValue;
    }

    /**
     * {@inheritdoc}
     */
    public function findChecksForNameList(array $nameList)
    {
        $retValue = array();
        foreach ($nameList as $name) {
            $retValue = array_merge($retValue, $this->findChecksForName($name));
        }

        return $retValue;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllChecks()
    {
        $retValue = array();
        foreach ($this->checkDataHolder->getAllChecks() as $checkData) {
            $retValue[] = $this->container->get($checkData[0]);
        }

        return $retValue;
    }

    /**
     * Gets a check from the container.
     *
     * @param string $name The check's service id
     *
     * @throws CheckNotFoundException if the check is not registered as a service
     *
     * @return CheckInterface
     */
    protected function lookupCheckByServiceId($name)
    {
        try {
            return $this->container->get($name);
        } catch (\InvalidArgumentException $e) {
            throw new CheckNotFoundException('Check not found: '.$name, 0, $e);
        }
    }
}
