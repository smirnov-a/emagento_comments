<?php

namespace Emagento\Comments\Block\Adminhtml\Review\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Emagento\Comments\Helper\Constants;

class BackButton implements ButtonProviderInterface
{
    /** @var Context */
    private Context $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'      => __('Back'),
            'on_click'   => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class'      => 'back',
            'sort_order' => 10,
        ];
    }

    /**
     * Get Back Url
     *
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl(Constants::LOCAL_COMMENT_GRID_PATH);
    }
}
