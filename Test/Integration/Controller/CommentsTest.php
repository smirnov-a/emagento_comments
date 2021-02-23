<?php
/**
 * Integration tests
 */
declare(strict_types=1);

namespace Emagento\Comments\Test\Integration\Controller;

use Magento\TestFramework\TestCase\AbstractController;

class CommentsTest extends AbstractController
{
    /** @var \Magento\TestFramework\ObjectManager */
    protected $_objectManager;

    /**
     * @inheridoc
     */
    public function setUp(): void
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store local_comments/flamp/is_enabled 1
     * @magentoConfigFixture default_store local_comments/settings/is_enabled 1
     * @magentoConfigFixture default_store local_comments/flamp/flamp_id 70000001017466092
     * @magentoCache config disabled
     * @magentoDataFixture Emagento_Comments::Test/Integration/_files/rating.php
     */
    public function testCommentsAction()
    {
        /** @var \Emagento\Comments\Model\Remote\Flamp */
        $flamp = $this->_objectManager->create('Emagento\Comments\Model\Remote\Flamp');   //echo get_class($flamp); exit;
        $cnt = $flamp->getComments();
        $this->assertGreaterThan(0, $cnt);
    }
}
