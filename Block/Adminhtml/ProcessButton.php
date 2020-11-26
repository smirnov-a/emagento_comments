<?php

namespace Local\Comments\Block\Adminhtml;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ProcessButton /*extends GenericButton*/ implements ButtonProviderInterface
{
    protected $_urlInterface;

    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_urlInterface = $urlInterface;
    }

    public function getButtonData()
    {
        return [
            'label' => __('Load reviews'),
            'class' => 'save primary',
            'on_click' => '',   // alert("test")
            'data_attribute' => [
                'mage-init' => [
                    'Local_Comments/js/button' => [
                        'url' =>  $this->_urlInterface->getUrl(
                            'local_comments/reviews/load',
                            ['_scope' => 1, '_nosid' => true]
                        )
                    ],
                    //'Magento_Ui/js/form/button-adapter' => [
                    //    'actions' => [
                    //        [
                    //            'targetName' => 'Local_Comments/js/button',    //'local_comments_button',
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
