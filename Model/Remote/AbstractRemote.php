<?php

namespace Local\Comments\Model\Remote;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AbstractRemote
 */
abstract class AbstractRemote
{
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
     * @var \Local\Comments\Model\ResourceModel\Review
     */
    protected $_reviewsResource;
    /**
     * @var \Local\Comments\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var DateTime
     */
    protected $dateTime;

    private $_globalEnabled;

    /**
     * AbstractRemote constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Local\Comments\Model\ResourceModel\Review $reviewResource
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ZendClientFactory $httpClientFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Local\Comments\Model\ResourceModel\Review $reviewResource,
        \Local\Comments\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DateTime $dateTime
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
        // активность взять из конфига
        $this->_globalEnabled = $this->_scopeConfig->getValue(
            'local_comments/settings/is_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Собственно загрузка комментариев
     *
     * @return int кол-во загруженных комментариев
     */
    public function getComments()
    {
        return 0;
    }

    /**
     * Возвразщает ссылку на сервис для загрузки комментариев
     *
     * @return string
     */
    public function getUrl()
    {
        return '';
    }

    /**
     * Вовзаращает парметры для http запроса
     *
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * Глобально выключены или нет комментари к магазину в админке
     *
     * @return bool
     */
    public function isGlobalEnabled()
    {
        return (bool)$this->_globalEnabled;
    }

    /**
     * Включен опеределенный канал комментариев или нет
     *
     * @return bool
     */
    public function isEnabled()
    {
        return false;
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
     * @throws Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get stores
     *
     * @return array
     */
    public function getStores()
    {
        $stores = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $stores[] = $store->getId();
        }

        return $stores;
    }
}
