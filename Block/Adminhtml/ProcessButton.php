<?php

namespace Emagento\Comments\Block\Adminhtml;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Emagento\Comments\Helper\Constants;

class ProcessButton implements ButtonProviderInterface
{
    /** @var UrlInterface */
    protected UrlInterface $backendUrlBuilder;
    /** @var LayoutInterface */
    protected LayoutInterface $layout;

    /**
     * @param UrlInterface $urlInterface
     * @param LayoutInterface $layout
     */
    public function __construct(
        UrlInterface $urlInterface,
        LayoutInterface $layout
    ) {
        $this->backendUrlBuilder = $urlInterface;
        $this->layout = $layout;
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'          => __('Load Reviews'),
            'class'          => 'save primary',
            'on_click'       => '',
            'data_attribute' => [
                'mage-init' => [
                    'Emagento_Comments/js/button' => [
                        'url' => $this->backendUrlBuilder->getUrl(Constants::LOCAL_COMMENT_REVIEW_LOAD_PATH),
                    ],
                ]
            ],
            'sort_order'     => 90,
        ];
    }
}
