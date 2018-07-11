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

use ChameleonSystem\SanityCheckBundle\Resolver\CheckDataHolderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SanityCheckInfoCommand lists information on the registered sanity checks.
 */
class SanityCheckInfoCommand extends Command
{
    /**
     * @var CheckDataHolderInterface
     */
    private $checkDataHolder;

    public function __construct(CheckDataHolderInterface $checkDataHolder)
    {
        parent::__construct();
        $this->checkDataHolder = $checkDataHolder;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('chameleon_system:sanitycheck:info')
            ->setDescription('Lists all defined sanity checks')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command lists all defined sanity checks:

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
        $checkList = $this->checkDataHolder->getBundleCheckData();

        $table = new Table($output);
        $table->setHeaders(array('Bundle', 'Registered checks'));
        foreach ($checkList as $bundle => $checksInBundle) {
            $table->addRow(
                array(
                    $bundle,
                    join(
                        "\n",
                        array_map(
                            function ($element) {
                                return $element[0];
                            },
                            $checksInBundle
                        )
                    ),
                )
            );
        }
        $table->render();
    }
}
