<?php
declare(strict_types=1);

namespace Emagento\Comments\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

class JobCommand extends SymfonyCommand
{
    /**
     * Type of comment yandex/flamp/etc default "all"
     */
    const TYPE_ARGUMENT = 'type';

    /**
     * Object manager factory
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    /**
     * JobCommand constructor.
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory
    ) {
        $this->objectManagerFactory = $objectManagerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::TYPE_ARGUMENT,
                null,
                InputOption::VALUE_OPTIONAL,
                'Type [yandex/flamp/all]',
                'all'
            )
        ];
        $this->setName('local:do_review_job')
            ->setDescription('Retrive remote comments (Yandex, Flamp etc)')
            ->setDefinition($options);
            /*
            ->setDefinition([
                new InputArgument(
                    self::TYPE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Type',
                    'all'
                ),
            ]);
            */
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types = ['yandex', 'flamp', 'all'];
        $omParams = []; // filter_input(INPUT_SERVERR, 'var') $_SERVER;
        $omParams['REMOTE_ADDR'] = '127.0.0.1';
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;  //var_dump($omParams); exit;
        $objectManager = $this->objectManagerFactory->create($omParams);

        $type = $input->getOption(self::TYPE_ARGUMENT); //var_dump($type); exit;
        if (!in_array($type, $types)) {
            $output->writeln('Error argument "' . self::TYPE_ARGUMENT . '": [' . implode('/', $types) . ']');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $types = array_filter(
            $types,
            function ($v) use ($type) {
                if ($type == 'all') {
                    return $v != 'all';
                } else {
                    return $v == $type;
                }
            }
        );
        $cnt = 0;
        foreach ($types as $t) {
            $class = 'Emagento\Comments\Model\Remote\\' . ucfirst($t);  //var_dump($class); exit;
            echo $class."\n";
            $obj = $objectManager->create($class);
            $cnt += $obj->getComments();
        }
        $output->writeln('<info>Success. Processed ' . $cnt . ' review(s)');
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        /*
        die('qqq');
        $factory = $objectManager->create(\Magento\Review\Model\ReviewFactory::class);
        $review = $factory->create()->load(6);
        $review
            ->setParentId(2)
            ->save();
        die('here');
        $collectionFactory = $objectManager->create(\Emagento\Comments\Model\ResourceModel\Review\CollectionFactory::class);
        $collection = $collectionFactory->create();
        $reviewCollection = $collection->getItems();
        foreach ($reviewCollection as $review) {
            echo $review->getId()."\n";
            $review->setPath('1/1/1')->save();
            die('qqq');
        }
        $output->writeln('<info>Type ' . $type . '!</info>');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        */
    }
}
