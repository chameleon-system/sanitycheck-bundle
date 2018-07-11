<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle;

use ChameleonSystem\SanityCheckBundle\DependencyInjection\Compiler\AddCheckOutputsPass;
use ChameleonSystem\SanityCheckBundle\DependencyInjection\Compiler\AddSanityChecksPass;
use ChameleonSystem\SanityCheckBundle\DependencyInjection\Compiler\AddTranslationsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ChameleonSystemSanityCheckBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddSanityChecksPass());
        $container->addCompilerPass(new AddCheckOutputsPass());
        $container->addCompilerPass(new AddTranslationsPass());
    }
}
