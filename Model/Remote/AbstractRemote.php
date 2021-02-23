<?php

namespace Emagento\Comments\Model\Remote;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AbstractRemote
 */
abstract class AbstractRemote
{
    const TYPE = '';     // flamp/yandex/etc
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;
    /**
     * @var ZendClientFactory
     */
    private $_httpClientFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_jsonSerializer;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;
    /**
     * @var \Emagento\Comments\Model\ResourceModel\Review
     */
    protected $_reviewsResource;
    /**
     * @var \Emagento\Comments\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;
    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $_optionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var array
     */
    protected $_ratingOptions;
    /**
     * @var int
     */
    protected $_ratingId;
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var array
     */
    protected $_stores;
    /**
     * @var array
     */
    protected $_workData;

    /**
     * AbstractRemote constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Emagento\Comments\Model\ResourceModel\Review $reviewResource
     * @param \Emagento\Comments\Model\ReviewFactory $reviewFactory
     * @param \Emagento\Comments\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
     * @param int|null $storeId
     * @param array|null $stores
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ZendClientFactory $httpClientFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Emagento\Comments\Model\ResourceModel\Review $reviewResource,
        \Emagento\Comments\Model\ReviewFactory $reviewFactory,
        \Emagento\Comments\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DateTime $dateTime,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory,
        ?int $storeId = null,
        ?array $stores = null
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_jsonSerializer = $jsonSerializer;
        $this->_escaper = $escaper;
        $this->_filterManager = $filterManager;
        $this->_reviewFactory = $reviewFactory;
        $this->_reviewsResource = $reviewResource;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->_optionFactory = $optionFactory;
        //
        $this->_storeId = !is_null($storeId) ? $storeId : $this->getStoreId();
        $this->_stores = !is_null($stores) ? $stores : $this->getStores();
    }

    /**
     * Заполняет массив со значениями рейтинга
     * @param null|array при тестах этот массив может прийти параметром
     */
    public function fillRatingOptions($options = [])
    {
        if ($options) {
            $this->_ratingOptions = $options;
            return;
        }
        // rating_id попробовать взять из конфига
        $this->_ratingId = $this->getConfigCommonValue('rating_id');  //var_dump($this->_ratingId); exit;
        if (!$this->_ratingId) {
            $connection = $this->_ratingFactory->create()->getResource()->getConnection();
            $select = $connection
                ->select()
                ->from('rating', ['rating_id'])
                ->where('entity_id=?', \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                ->limit(1);
            $this->_ratingId = $connection->fetchOne($select);
        }
        // заполнить ratingOptions для этого рейтинга
        /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collectionOptions */
        $collectionOptions = $this->_optionFactory->create();  //var_dump($collection); exit;
        $collectionOptions
            ->addRatingFilter($this->_ratingId)
            ->setPositionOrder();

        foreach ($collectionOptions as $option) {
            $this->_ratingOptions[$this->_ratingId][] = $option->getId();     // [6 => [21, 22, 23, 24, 25]]
        }
        //var_dump($this->_ratingOptions); exit;
    }

    /**
     * Собственно загрузка комментариев
     *
     * @return int кол-во загруженных комментариев
     */
    public function getComments() : int
    {
        return 0;
    }

    /**
     * Рабочие данные
     * @param array $data
     */
    public function setWorkData($data)
    {
        $this->_workData = $data;
    }

    /**
     * Возвразщает ссылку на сервис для загрузки комментариев
     *
     * @return string
     */
    public function getUrl() : string
    {
        return '';
    }

    /**
     * Вовзаращает парметры для http запроса
     *
     * @return array
     */
    public function getParams() : array
    {
        return [];
    }

    /**
     * Глобально выключены или нет комментари к магазину в админке
     *
     * @return bool
     */
    public function isGlobalEnabled() : bool
    {
        // local_comments/settings/is_enabled
        return (bool)$this->getConfigCommonValue('is_enabled');
    }

    /**
     * Работа с запросом на никзом уровне
     * @param string $type тип GET/POST
     * @return bool|array массив с отзывами
     */
    public function doRequest($type = ZendClient::GET)
    {
        $client = $this->_httpClientFactory->create();  //var_dump($client); exit;
        $url = $this->getUrl();
        $params = $this->getParams();
        try {
            $client->setUri($url);
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            //$client->setRawData($this->json->serialize($request), 'application/json');
            $client->setMethod($type);
            if ($params) {
                $client->setParameterGet($params);
            }
            //$client->setMethod(\Zend_Http_Client::PUT);
            //$client->setHeaders(\Zend_Http_Client::CONTENT_TYPE, 'application/json');
            //$client->setHeaders('Accept','application/json');
            //$client->setHeaders("Authorization","Bearer 1212121212121");
            //$client->setParameterPost($params); //json
            $responseBody = $client->request()
                ->getBody();
            //$q = $this->_jsonSerializer->unserialize($responseBody); var_dump($q); exit;
            // строку json в массив
            return $this->_jsonSerializer->unserialize($responseBody);

        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return false;
    }

    /**
     * Get store identifier
     *
     * @return int
     * @throws \Exception
     */
    public function getStoreId() : int
    {
        return $this->_storeManager->getStore()->getId();
        //$store = $this->_storeManager->getStore();
        //if ($store) {
        //    return $store->getId();
        //}
        //return 0;
    }

    /**
     * Get stores
     *
     * @return array
     */
    public function getStores() : array
    {
        $stores = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $stores[] = $store->getId();
        }
        //$stores = [];
        //$_stores = $this->_storeManager->getStores();
        //if ($_stores) {
        //    foreach ($_stores as $store) {
        //        $stores[] = $store->getId();
        //    }
        //}
        return $stores;
    }

    /**
     * Get value 'local_comments/settings/<item>'
     *
     * @param string $item
     * @return int|null|string
     */
    public function getConfigCommonValue($item)
    {
        return $this->_scopeConfig->getValue(
            'local_comments/settings/' . $item,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get value from core_config
     * @param string $item
     * @return int|null|string
     */
    public function getConfigValue($item)
    {
        return $this->_scopeConfig->getValue(
            'local_comments/' . static::TYPE . '/' . $item,             // 'local_comments/flamp/enabled'
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Включен опеределенный канал комментариев или нет в админке 'local_comments/flamp/is_enabled'
     * @return bool
     */
    public function isEnabled() : bool
    {
        // local_comments/flamp/is_enabled
        return (bool)$this->getConfigValue('is_enabled');
    }
}
