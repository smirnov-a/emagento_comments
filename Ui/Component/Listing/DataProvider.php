<?php

namespace Emagento\Comments\Ui\Component\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Emagento\Comments\Model\ResourceModel\Review\Collection;
use Emagento\Comments\Helper\Data;

/**
 * Class DataProvider for Emagento Comments
 *
 * @method Collection getCollection
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface $request
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * Получение данных. Здесь фильтр по типу комментария
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $collection
            ->addFieldToFilter('entity_id', Data::REVIEW_ENTITY_TYPE_STORE)
            ->addStoreData();
        $data = $collection->toArray();
        /*
        foreach ($data['items'] as $key => $item) {
            if (isset($item['country_id']) && !isset($item['country'])) {
                $data['items'][$key]['country'] = $this->countryDirectory->loadByCode($item['country_id'])->getName();
            }
        }
        */

        return $data;
    }
}
