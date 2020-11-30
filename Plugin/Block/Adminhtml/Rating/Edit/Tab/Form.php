<?php

namespace Emagento\Comments\Plugin\Block\Adminhtml\Rating\Edit\Tab;

use Magento\Framework\Registry;

class Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function aroundGetFormHtml(
        \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form $subject,
        \Closure $proceed
    ) {
        $form = $subject->getForm();
        if (is_object($form)) {
            $ratingData = $this->registry->registry('rating_data');
            $fieldset = $form->getElement('rating_form');  //echo get_class($fieldset); exit;
            if ($fieldset) {
                $fieldset->addField(
                    'entity_id',
                    'select',   // 'text',
                    [
                        'name' => 'entity_id',
                        'label' => __('Entity Type'),
                        'title' => __('Entity Type'),
                        'required' => true,
                        'value' => $ratingData['entity_id'],
                        'class' => 'required-entry',
                        'values' => [
                            ['label' => 'Product', 'value' => 1],
                            ['label' => 'Store', 'value' => 4],
                        ]
                    ]
                );

                //if ($ratingData) {
                //    $form->setValues($ratingData);
                //}
            }
        }

        return $proceed();
    }
    /*
    public function aroundAddRatingFiledSet(
        \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form $subject,
        \Closure $proceed
    ) {

        return $proceed();
    }
    */
}
