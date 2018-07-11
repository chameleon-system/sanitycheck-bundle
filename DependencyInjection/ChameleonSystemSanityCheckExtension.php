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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * ChameleonSystemSanityCheckExtension.
 */
class ChameleonSystemSanityCheckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!$config['enabled']) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));

        if (true === isset($config['output']['mail']['enabled']) && $config['output']['mail']['enabled']) {
            $container->setParameter('chameleon_system_sanity_check.mailer.from', $config['output']['mail']['from']);
            $container->setParameter('chameleon_system_sanity_check.mailer.to', $config['output']['mail']['to']);

            switch ($config['output']['mail']['implementation']) {
                case 'phpmailer':
                    $this->initPhpmailer($container, $loader, $config);
                    break;
            }
        }
        $loader->load('services.xml');

        if (array_key_exists('output', $config) && array_key_exists(
                'log',
                $config['output']
            ) && $config['output']['log']['logger']
        ) {
            $logOutput = $container->getDefinition('chameleon_system_sanity_check.check_output.log');
            $logOutput->replaceArgument(1, new Reference($config['output']['log']['logger']));
        }
    }

    private function initPhpmailer(ContainerBuilder $container, FileLoader $loader, array $config)
    {
        $loader->load('phpmailer.xml');
        $phpmailer = $container->getDefinition('chameleon_system_sanity_check.check_output.phpmailer');

        if ($mailerService = $config['output']['mail']['service']) {
            $phpmailer->addMethodCall('setMailerServiceId', array($mailerService));
        }
        if ($level = $config['output']['mail']['level']) {
            $phpmailer->addMethodCall('setLevel', array($level));
        }
    }
}
