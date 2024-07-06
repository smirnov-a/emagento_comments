<?php

namespace Emagento\Comments\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class LoaderTemplate extends Template
{
    /**
     * Get ImgLoader Url
     *
     * @return string
     */
    public function getImgLoaderUrl(): string
    {
        return $this->_assetRepo->getUrl('Emagento_Comments::images/ajax-loader.gif');
    }
}
