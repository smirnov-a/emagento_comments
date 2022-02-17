<?php

namespace Emagento\Comments\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const REVIEW_ENTITY_TYPE_STORE = 4;

    public function getSourceOptionArray()
    {
        $result = [];
        foreach ($this->getSources() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getSources()
    {
        return [
            'local'  => 'Local',
            'flamp'  => 'Flamp',
            'yandex' => 'Yandex',
        ];
    }
}
