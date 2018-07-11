<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SanityCheckBundle\Command;

use ChameleonSystem\SanityCheck\Formatter\OutputFormatterInterface;
use ChameleonSystem\SanityCheck\Output\AbstractTranslatingCheckOutput;
use ChameleonSystem\SanityCheck\Output\DefaultCheckOutput;
use ChameleonSystem\SanityCheck\Suite\CheckSuiteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PerformSanityCheckSuiteCommand performs sanity check suites from the console.
 */
class PerformSanityCheckSuiteCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DefaultCheckOutput
     */
    private $defaultCheckOutput;
    /**
     * @var OutputFormatterInterface
     */
    private $consoleOutputFormatter;

    public function __construct(
        ContainerInterface $container,
        DefaultCheckOutput $defaultCheckOutput,
        OutputFormatterInterface $consoleOutputFormatter
    ) {
        parent::__construct();
        $this->container = $container;
        $this->defaultCheckOutput = $defaultCheckOutput;
        $this->consoleOutputFormatter = $consoleOutputFormatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('chameleon_system:sanitycheck:suite')
            ->setDefinition(
                array(
                    new InputOption(
                        'which',
                        'w',
                        InputOption::VALUE_REQUIRED,
                        'Defines a check suite to execute. Note that the output of a check suite is defined in its config and might not be suitable for the console'
                    ),
                    new InputOption(
                        'locale',
                        'l',
                        InputOption::VALUE_OPTIONAL,
                        "The locale for console output. Defaults to 'en'"
                    ),
                    new InputOption(
                        'console',
                        'c',
                        InputOption::VALUE_OPTIONAL,
                        "'true' if all check outputs shall be redirected to the console. Defaults to 'true'"
                    ),
                )
            )
            ->setDescription('Executes sanity check suites')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command executes sanity check suites. A check suite is a combination of multiple checks and an output method, and allows for short-hand reference.

<info>php %command.full_name% --env=dev</info>
<info>php %command.full_name% --env=prod --no-debug</info>

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suiteId = $input->getOption('which');
        $locale = $input->getOption('locale');
        $redirectOutput = 'false' !== $input->getOption('console') ? true : false;

        if (!$suiteId) {
            $output->writeln("You need to specify a check suite using the 'which' parameter");

            return;
        }

        $this->defaultCheckOutput->setOutputFormatter($this->consoleOutputFormatter);
        if ($locale) {
            $this->defaultCheckOutput->setLocale($locale);
        }

        /**
         * @var CheckSuiteInterface $suite
         */
        $suite = $this->container->get($suiteId);

        if ($redirectOutput) {
            $suite->setOutputs(array($this->defaultCheckOutput));
        } else {
            if ($locale) {
                $outputs = $suite->getOutputs();
                foreach ($outputs as $suiteOutput) {
                    if ($locale && ($suiteOutput instanceof AbstractTranslatingCheckOutput)) {
                        $suiteOutput->setLocale($locale);
                    }
                }
            }
        }

        if ($redirectOutput) {
            @ob_start();
            $suite->execute();
            $output->writeln(@ob_get_clean());
        } else {
            $suite->execute();
        }
    }
}
