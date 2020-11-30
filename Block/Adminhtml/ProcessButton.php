<?php

namespace Emagento\Comments\Block\Adminhtml;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ProcessButton /*extends GenericButton*/ implements ButtonProviderInterface
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrlBuilder;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlInterface
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
        $html .=   '<p style="margin:0; position:absolute; top:50%; left:50%; margin-right:-50%; transform:translate(-50%,-50%)">';
        $html .=      '<span>Processing... </span><img src="' . $imgLoader . '" alt="Ajax loader" />';
        $html .=   '</p>';
        $html .= '</div>';

        return [
            'label' => __('Load reviews'),
            'class' => 'save primary',
            'on_click' => '',   // alert("test")
            'data_attribute' => [
                'mage-init' => [
                    'Emagento_Comments/js/button' => [
                        'url' =>  $this->_backendUrlBuilder->getUrl(
                            'local_comments/reviews/load'//,
                            //['_scope' => 1, '_nosid' => true]
                        ),
                        'html_templ' => $html,
                    ],
                    //'Magento_Ui/js/form/button-adapter' => [
                    //    'actions' => [
                    //        [
                    //            'targetName' => 'Emagento_Comments/js/button',
                    //            'actionName' => 'processReviews',
                    //        ]
                    //    ],
                    //],
                ]
            ],
            //'data_attribute' => [
            //    'mage-init' => ['button' => ['event' => 'save']],
            //    'form-role' => 'save',
            //],
            'sort_order' => 90,
        ];
    }
}
