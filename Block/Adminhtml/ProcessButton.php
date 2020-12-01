<?php

namespace Emagento\Comments\Block\Adminhtml;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Model\UrlInterface;

class ProcessButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $_backendUrlBuilder;

    public function __construct(
        UrlInterface $urlInterface
    ) {
        $this->_backendUrlBuilder = $urlInterface;
    }

    public function getButtonData()
    {
        $imgLoader = $this->_backendUrlBuilder->getDirectUrl(
            'wysiwyg/ajax-loader.gif',
            ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]
        );
        $html =  '<div style="height:5em; position:relative">';
        $html .=   '<p style="margin:0; position:absolute; top:50%; left:50%; margin-right:-50%; ';
        $html .=             'transform:translate(-50%,-50%)">';
        $html .=      '<span>Processing... </span><img src="' . $imgLoader . '" alt="Ajax loader" />';
        $html .=   '</p>';
        $html .= '</div>';

        return [
            'label' => __('Load reviews'),
            'class' => 'save primary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Emagento_Comments/js/button' => [
                        'url' =>  $this->_backendUrlBuilder->getUrl(
                            'local_comments/reviews/load'
                        ),
                        'html_templ' => $html,
                    ],
                ]
            ],
            'sort_order' => 90,
        ];
    }
}
