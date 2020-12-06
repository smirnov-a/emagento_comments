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
    protected $_scopeConfig;
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
     * AbstractRemote constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Emagento\Comments\Model\ResourceModel\Review $reviewResource
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param DateTime $dateTime
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
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
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DateTime $dateTime,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_jsonSerializer = $jsonSerializer;
        $this->_escaper = $escaper;
        $this->_filterManager = $filterManager;
        $this->_reviewsResource = $reviewResource;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->_optionFactory = $optionFactory;
        //
        $this->_storeId = $this->getStoreId();
        $this->_stores = $this->getStores();
        // rating_id из конфига
        $this->_ratingId = $this->getConfigCommonValue('rating_id');     // 6
        // заполнить ratingOptions для этого рейтинга
        $collection = $this->_optionFactory->create();
        $collection
            ->addRatingFilter($this->_ratingId)
            ->setPositionOrder();
        foreach ($collection as $option) {
            $this->_ratingOptions[$this->_ratingId][] = $option->getId();     // [6 => [21, 22, 23, 24, 25]]
        }
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
        return (bool)$this->getConfigCommonValue('is_enabled');
    }

    /**
     * Отправляет запрос
     *
     * @param string $type тип GET/POST
     *
     * @return bool|array массив с отзывами
     */
    public function doRequest($type = ZendClient::GET)
    {
        $client = $this->_httpClientFactory->create();
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
        return (bool)$this->getConfigValue('is_enabled');
    }
}
