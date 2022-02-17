<?php
/**
 * Unit test
 */
declare(strict_types=1);

namespace Emagento\Comments\Test\Unit\Model\Remote;

use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\FilterManager;
use Emagento\Comments\Model\ResourceModel\Review;
use Emagento\Comments\Model\ReviewFactory;
use Emagento\Comments\Model\Review as EmagentoReview;
use Emagento\Comments\Model\RatingFactory;
use Emagento\Comments\Model\Rating as EmagentoRating;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Emagento\Comments\Model\Remote\Flamp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Collection as OptionCollection;
//use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlampTest extends TestCase
{
    /** @var Flamp */
    protected $flampModel;
    protected $logger;
    protected $storeManager;
    protected $scopeConfig;
    protected $zendClient;
    protected $jsonSerializer;
    protected $escaper;
    protected $filterManager;
    protected $reviewResource;
    protected $reviewFactory;
    protected $review;
    protected $ratingFactory;
    protected $rating;
    protected $dateTime;
    protected $optionCollectionMock;
    protected $optionCollectionFactoryMock;

    /**
     * @inheridocs
     */
    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods([
                'emergency',
                'alert',
                'critical',
                'error',
                'warning',
                'notice',
                'info',
                'debug',
                'log'
            ])
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        // https://stackoverflow.com/questions/56935534/how-to-do-dependency-injection-in-magento-2-phpunit-tests
        $valueMap = [
            ['local_comments/flamp/is_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, true],
            ['local_comments/settings/is_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, true],
            ['local_comments/settings/rating_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, 6],
        ];
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeConfig
            ->method('getValue')
            ->willReturnMap($valueMap);
        $this->zendClient = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonSerializer = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->getMock();
        $this->filterManager = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reviewResource = $this->getMockBuilder(Review::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reviewFactory = $this->getMockBuilder(ReviewFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->review = $this->getMockBuilder(EmagentoReview::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->ratingFactory = $this->getMockBuilder(RatingFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rating = $this->getMockBuilder(EmagentoRating::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        // https://magento.stackexchange.com/questions/120209/how-to-properly-use-getcollectionmock
        $this->optionCollectionFactoryMock = $this->getMockBuilder(OptionCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionCollectionMock = $objectManager->getCollectionMock(OptionCollection::class, []);
        $this->optionCollectionMock->method('toOptionArray')->willReturn([6 => [21, 22, 23, 24, 25]]);
        $this->optionCollectionMock->method('toOptionArray');       // expects($this->atLeastOnce())
        $this->optionCollectionMock->method('addRatingFilter')->willReturnSelf();
        $this->optionCollectionMock->method('setPositionOrder')->willReturnSelf();
        $this->optionCollectionFactoryMock->method('create')->willReturn($this->optionCollectionMock);

        $this->reviewFactory->method('create')->willReturn($this->review);
        $this->review->method('setEntityId')->willReturnSelf();
        $this->review->method('setSource')->willReturnSelf();
        $this->review->method('setSourceId')->willReturnSelf();
        $this->review->method('setCreatedAt')->willReturnSelf();
        $this->review->method('setUpdatedAt')->willReturnSelf();
        $this->review->method('setEntityPkValue')->willReturnSelf();
        $this->review->method('setCustomerId')->willReturnSelf();
        $this->review->method('setStatusId')->willReturnSelf();
        $this->review->method('setTitle')->willReturnSelf();
        $this->review->method('setDetail')->willReturnSelf();
        $this->review->method('setNickname')->willReturnSelf();
        $this->review->method('setStoreId')->willReturnSelf();
        $this->review->method('setStores')->willReturnSelf();
        $this->review->method('save')->willReturnSelf();

        $this->ratingFactory->method('create')->willReturn($this->rating);
        $this->rating->method('setRatingId')->willReturnSelf();
        $this->rating->method('setReviewId')->willReturnSelf();
        $this->rating->method('addOptionVote')->willReturnSelf();

        //$objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->flampModel = $objectManager->getObject(
            Flamp::class,
            [
                'logger'            => $this->logger,
                'scopeConfig'       => $this->scopeConfig,
                'httpClientFactory' => $this->zendClient,
                'jsonSerializer'    => $this->jsonSerializer,
                'escaper'           => $this->escaper,
                'filterManager'     => $this->filterManager,
                'reviewResource'    => $this->reviewResource,
                'reviewFactory'     => $this->reviewFactory,
                'ratingFactory'     => $this->ratingFactory,
                'storeManager'      => $this->storeManager,
                'dateTime'          => $this->dateTime,
                'optionFactory'     => $this->optionCollectionFactoryMock,
                'storeId'           => 0,
                'stores'            => [0],
            ]
        );
    }

    /**
     * @dataProvider commentsDataProvider
     */
    public function testGetComments($a)
    {
        $this->flampModel->setWorkData($a);
        $this->flampModel->fillRatingOptions([6 => [21, 22, 23, 24, 25]]);
        $this->assertEquals(2, $this->flampModel->getComments());
    }

    /**
     * @return array
     */
    public function commentsDataProvider(): array
    {
        return [
            [
                ["meta" => [
                    "branch_rating" => 3.7,
                    "branch_reviews_count" => 49,
                    "code" => 200,
                    "next_link" => "https://api.reviews.2gis.com/2.0/branches/70000001017466092/reviews?fields=meta.providers%2Cmeta.branch_rating%2Cmeta.branch_reviews_count%2Cmeta.org_rating%2Cmeta.org_reviews_count\u0026is_advertiser=false\u0026limit=24\u0026offset_date=2018-10-05T15%3A28%3A51.0%2B07%3A00\u0026sort_by=date_created",
                    "org_rating" => 3.3,
                    "org_reviews_count" => 903,
                    "providers" => [
                        ["tag" => "flamp", "is_reviewable" => true],
                        ["tag" => "2gis", "is_reviewable" => true]
                    ]
                ],
                "reviews" => [
                    ["id" => "18918972",
                        "region_id" => 9,
                        "text" => "Распродажи я обожаю, люблю зайти и чтонибудь это-кое прикупить, в чем я всегда не промахиваюсь, советую вам зайти и глянуть, что-нибудь.",
                        "rating" => 5,
                        "provider" => "flamp",
                        "is_hidden" => false,
                        "hiding_type" => null,
                        "hiding_reason" => null,
                        "url" => "https://flamp.ru/r/70000001017466092/6686544",
                        "likes_count" => 0,
                        "comments_count" => 0,
                        "date_created" => "2021-01-27T22:03:14.0+07:00",
                        "date_edited" => null,
                        "object" => ["id" => "70000001017466092", "type" => "branch"],
                        "user" => [
                            "id" => "4150518",
                            "reviews_count" => 9,
                            "first_name" => "Полина Суворова",
                            "last_name" => null,
                            "name" => "Полина Суворова",
                            "provider" => "flamp",
                            "photo_preview_urls" => ["1920x" => "https://cdn1.flamp.ru/a20809798b1595306bdb65c16b04cdb8_1920.jpg", "320x" => "https://cdn1.flamp.ru/a20809798b1595306bdb65c16b04cdb8_320.jpg", "640x" => "https://cdn1.flamp.ru/a20809798b1595306bdb65c16b04cdb8_640.jpg", "64x64" => "https://cdn1.flamp.ru/a20809798b1595306bdb65c16b04cdb8_64_64.jpg", "url" => "https://cdn1.flamp.ru/a20809798b1595306bdb65c16b04cdb8.jpg"],
                            "url" => "http://flamp.ru/user4150518",
                            "additional_data" => ["email" => null]
                        ],
                        "official_answer" => [
                            "id" => "5266732",
                            "org_name" => "Галамарт, магазин постоянных распродаж",
                            "text" => "Добрый день, Уважаемый покупатель. Огромное спасибо за положительный  отзыв. Всегда рады видеть вас в нашем магазине. Будем рады видеть Вас в нашем магазине снова.",
                            "date_created" => "2021-02-01T16:47:04.0+07:00",
                            "logo_preview_urls" => [
                                "1920x" => "https://cdn1.flamp.ru/3a32126c0e5beac6bb35b1194c80c384_1920.jpg",
                                "320x" => "https://cdn1.flamp.ru/3a32126c0e5beac6bb35b1194c80c384_320.jpg",
                                "640x" => "https://cdn1.flamp.ru/3a32126c0e5beac6bb35b1194c80c384_640.jpg",
                                "64x64" => "https://cdn1.flamp.ru/3a32126c0e5beac6bb35b1194c80c384_64_64.jpg",
                                "url" => "https://cdn1.flamp.ru/3a32126c0e5beac6bb35b1194c80c384.jpg"]
                        ],
                        "photos" => [],
                        "additional_data" => [
                            "status_history" => [],
                            "has_unresolved_complaints" => false,
                            "pin_status" => "none",
                            "ip" => "",
                            "user_agent" => "",
                            "external_id" => "6686544",
                            "filtered_facts" => null,
                            "is_unrated_permanently" => null,
                            "is_liked" => null
                        ],
                        "on_moderation" => false,
                        "is_rated" => true
                    ],
                    ["id" => "18873089",
                        "region_id" => 9,
                        "text" => "Дурацкий магазин-цены высокие, того, что надо - нет. Нет подставки под крышки . Всякого барахла зато навалом. Зашла за нужными вещами ребёнку. Пирамидки нет, вкладышей нет, фигурок животных тоже нет",
                        "rating" => 2,
                        "provider" => "2gis",
                        "is_hidden" => false,
                        "hiding_type" => null,
                        "hiding_reason" => null,
                        "url" => "",
                        "likes_count" => 0,
                        "comments_count" => 0,
                        "date_created" => "2021-01-25T14:26:44.918796+07:00",
                        "date_edited" => null,
                        "object" => ["id" => "70000001017466092", "type" => "branch"],
                        "user" => [
                            "id" => "12285877",
                            "reviews_count" => 4,
                            "first_name" => "Анна Квашнина",
                            "last_name" => null,
                            "name" => "Анна Квашнина",
                            "provider" => "2gis",
                            "photo_preview_urls" => [
                                "1920x" => "https://i2.photo.2gis.com/images/profile/844424956409880_814f_1920x.jpg",
                                "320x" => "https://i2.photo.2gis.com/images/profile/844424956409880_814f_320x.jpg",
                                "640x" => "https://i2.photo.2gis.com/images/profile/844424956409880_814f_640x.jpg",
                                "64x64" => "https://i2.photo.2gis.com/images/profile/844424956409880_814f_64x64.jpg",
                                "url" => "https://i2.photo.2gis.com/images/profile/844424956409880_814f.jpg"
                            ],
                            "url" => "",
                            "additional_data" => ["email" => null]
                        ],
                        "official_answer" => null,
                        "photos" => [],
                        "additional_data" => [
                            "status_history" => [],
                            "has_unresolved_complaints" => false,
                            "pin_status" => "none",
                            "ip" => "",
                            "user_agent" => "",
                            "external_id" => null,
                            "filtered_facts" => null,
                            "is_unrated_permanently" => null,
                            "is_liked" => null
                        ],
                        "on_moderation" => false,
                        "is_rated" => true
                    ],
                ],
            ],
        ]];
    }
}
