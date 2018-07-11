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
use ChameleonSystem\SanityCheck\Handler\CheckHandlerInterface;
use ChameleonSystem\SanityCheck\Output\CheckOutputInterface;
use ChameleonSystem\SanityCheck\Output\DefaultCheckOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PerformSanityChecksCommand performs sanity checks from the console.
 */
class PerformSanityChecksCommand extends Command
{
    /**
     * @var CheckHandlerInterface
     */
    private $checkHandler;
    /**
     * @var DefaultCheckOutput
     */
    private $defaultCheckOutput;
    /**
     * @var OutputFormatterInterface
     */
    private $consoleOutputFormatter;

    public function __construct(
        CheckHandlerInterface $checkHandler,
        CheckOutputInterface $checkOutput,
        OutputFormatterInterface $consoleOutputFormatter
    ) {
        parent::__construct();
        $this->checkHandler = $checkHandler;
        $this->defaultCheckOutput = $checkOutput;
        $this->consoleOutputFormatter = $consoleOutputFormatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('chameleon_system:sanitycheck:check')
            ->setDefinition(
                array(
                    new InputOption(
                        'which',
                        'w',
                        InputOption::VALUE_OPTIONAL,
                        'Defines which checks to perform (comma-separated list; values can be bundle names to perform all checks for this bundle). Performs all checks if omitted.'
                    ),
                    new InputOption(
                        'locale',
                        'l',
                        InputOption::VALUE_OPTIONAL,
                        "The locale for console output in 2-letter ISO639-1 format. Defaults to 'en'"
                    ),
                )
            )
            ->setDescription('Performs sanity checks')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command performs sanity checks:

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
        if ($which = $input->getOption('which')) {
            $checkList = explode(',', $which);
            $outcomeList = $this->checkHandler->checkSome($checkList);
        } else {
            $outcomeList = $this->checkHandler->checkAll();
        }

        $this->defaultCheckOutput->setOutputFormatter($this->consoleOutputFormatter);

        if ($locale = $input->getOption('locale')) {
            $this->defaultCheckOutput->setLocale($locale);
        }

        @ob_start();
        foreach ($outcomeList as $outcome) {
            $this->defaultCheckOutput->gather($outcome);
        }
        $output->writeln(@ob_get_clean());
    }
}
