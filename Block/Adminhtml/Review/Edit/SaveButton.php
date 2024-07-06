<?php

namespace Emagento\Comments\Block\Adminhtml\Review\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save'),
            'class'          => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save'],
                    'Emagento_Comments/js/form/element/rating' => [],
                ],
                'form-role' => 'save',
            ],
            'sort_order'     => 90,
        ];
    }
}
