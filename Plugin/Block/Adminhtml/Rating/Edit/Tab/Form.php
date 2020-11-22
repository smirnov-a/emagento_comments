<?php

namespace Local\Comments\Plugin\Block\Adminhtml\Rating\Edit\Tab;

class Form
{
    public function aroundGetFormHtml(
        \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form $subject,
        \Closure $proceed
    ) {
        $form = $subject->getForm();
        if (is_object($form)) {
            $fieldset = $form->getElement('rating_form');  //echo get_class($fieldset); exit;
            if ($fieldset) {
                $fieldset->addField(
                    'entity_id',
                    'text',
                    [
                        'name' => 'entity_id',
                        'label' => __('Entity Type'),
                        'title' => __('Entity Type'),
                    ]
                );
                //$qq = $form->_coreRegistry->registry('rating_data'); var_dump($qq); exit;
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
