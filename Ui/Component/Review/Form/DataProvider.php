<?php

namespace Emagento\Comments\Ui\Component\Review\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /** @var DataPersistorInterface */
    protected DataPersistorInterface $dataPersistor;
    /** @var array */
    protected array $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->meta = $meta;
    }

    /**
     * Get Data
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        /** @var \Emagento\Comments\Model\Review $review */
        foreach ($this->collection->getItems() as $review) {
            $this->loadedData[$review->getId()] = $review->getData();
            $this->loadedData[$review->getId()]['author'] = $review->getAuthorInfo();
            $this->loadedData[$review->getId()]['ratings'] = $review->getRatingsData();
        }

        $data = $this->dataPersistor->get('local_review');
        if (!empty($data)) {
            $review = $this->collection->getNewEmptyItem();
            $review->setData($data);
            $this->loadedData[$review->getId()] = $review->getData();
            $this->dataPersistor->clear('local_review');
        }

        return $this->loadedData;
    }
}
