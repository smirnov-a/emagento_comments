<?php

namespace Local\Comments\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

class JobCommand extends Command
{
    /**
     * аргумент тип комментариев yandex/flamp/etc по умолчанию 'all'
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
        $this->setName('local:do_review_job')
            ->setDescription('Retrive remote comments (Yandex, Flamp etc)')
            ->setDefinition([
                new InputArgument(
                    self::TYPE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Type',
                    'all'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        $type = $input->getArgument(self::TYPE_ARGUMENT);
        $factory = $objectManager->create(\Magento\Review\Model\ReviewFactory::class);
        $review = $factory->create()->load(6);
        $review
            ->setParentId(2)
            ->save();
        die('here');
        $collectionFactory = $objectManager->create(\Local\Comments\Model\ResourceModel\Review\CollectionFactory::class);  //, ['parameters' => $params]);
        $collection = $collectionFactory->create();
        $reviewCollection = $collection->getItems();
        foreach ($reviewCollection as $review) {
            echo $review->getId()."\n";
            $review->setPath('1/1/1')->save();
            die('qqq');
        }
        $output->writeln('<info>Type ' . $type . '!</info>');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
