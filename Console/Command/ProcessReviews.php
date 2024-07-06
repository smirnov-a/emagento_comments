<?php

namespace Emagento\Comments\Console\Command;

use Magento\Framework\App\ObjectManager;
use Emagento\Comments\Exception\UnknownSourceException;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Emagento\Comments\Helper\Constants;
use Emagento\Comments\Helper\Data as Helper;
use Emagento\Comments\Model\Review\Remote\Processor as RemoteProcessor;

class ProcessReviews extends SymfonyCommand
{
    private const TYPE_ARGUMENT = 'type';
    private const COMMAND_NAME = 'reviews:process_reviews';

    /** @var RemoteProcessor */
    private RemoteProcessor $processor;
    /** @var State */
    private State $state;
    /** @var Helper */
    private Helper $helper;

    /**
     * @param State $state
     * @param RemoteProcessor $processor
     * @param Helper $helper
     */
    public function __construct(
        State $state,
        RemoteProcessor $processor,
        Helper $helper = null,
    ) {
        $this->state = $state;
        $this->processor = $processor;
        $this->helper = $helper ?: ObjectManager::getInstance()->get(Helper::class);
        parent::__construct();
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure(): void
    {
        $options = [
            new InputOption(
                self::TYPE_ARGUMENT,
                null,
                InputOption::VALUE_OPTIONAL,
                $this->getOptionTypeDescription(),
                Constants::TYPE_ALL
            )
        ];
        $this->setName(self::COMMAND_NAME)
            ->setDescription($this->getCommandDescription())
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws UnknownSourceException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);

        $types = $this->helper->getRemoteTypes(true);
        $type = $input->getOption(self::TYPE_ARGUMENT);
        if (!in_array($type, $types)) {
            $output->writeln(
                'Error argument "' . self::TYPE_ARGUMENT . '": '
                . '[' . implode('/', $types) . ']'
            );
            return Cli::RETURN_FAILURE;
        }

        $types = array_filter(
            $types,
            function ($v) use ($type) {
                return $type == Constants::TYPE_ALL
                    ? $v != Constants::TYPE_ALL
                    : $v == $type;
            }
        );

        $cnt = 0;
        foreach ($types as $type) {
            $cnt += $this->processor->processRemoteReviews($type);
        }
        $output->writeln('<info>Success. Processed ' . $cnt . ' review(s)');

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Get Options Description
     *
     * @return string
     */
    private function getOptionTypeDescription(): string
    {
        return sprintf('Type [%s]', join('/', $this->helper->getRemoteTypes(true)));
    }

    /**
     * Get Command Description
     *
     * @return string
     * // phpcs:disable
     */
    private function getCommandDescription(): string
    {
        $types = array_map(
            function ($type) {
                return ucfirst($type);
            },
            $this->helper->getRemoteTypes()
        );

        return sprintf('Retrieve remote comments (%s)', join('/', $types));
    }
}
