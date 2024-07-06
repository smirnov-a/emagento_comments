<?php

namespace Emagento\Comments\Plugin\Block\Adminhtml\Rating\Edit\Tab;

use Emagento\Comments\Model\Rating\Form\DataProvider as RatingDataProvider;
use Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form;
use Emagento\Comments\Model\ResourceModel\Review\Entity\CollectionFactory as ReviewEntityCollectionFactory;
use Closure;

class FormPlugin
{
    /** @var RatingDataProvider */
    private RatingDataProvider $ratingDataProvider;
    /** @var ReviewEntityCollectionFactory */
    private ReviewEntityCollectionFactory $reviewCollectionFactory;

    /**
     * @param RatingDataProvider $ratingDataProvider
     * @param ReviewEntityCollectionFactory $reviewCollectionFactory
     */
    public function __construct(
        RatingDataProvider $ratingDataProvider,
        ReviewEntityCollectionFactory $reviewCollectionFactory
    ) {
        $this->ratingDataProvider = $ratingDataProvider;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * Around Form Html
     *
     * @param Form $subject
     * @param Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        Form $subject,
        Closure $proceed
    ) {
        $form = $subject->getForm();
        if (is_object($form)) {
            $ratingData = $this->ratingDataProvider->getRatingData();
            if ($fieldset = $form->getElement('rating_form')) {
                $fieldset->addField(
                    'entity_id',
                    'select',
                    [
                        'name'     => 'entity_id',
                        'label'    => __('Entity Type'),
                        'title'    => __('Entity Type'),
                        'required' => true,
                        'value'    => $ratingData['entity_id'] ?? null,
                        'class'    => 'required-entry',
                        'values'   => $this->getReviewEntityData(),
                    ]
                );
            }
        }

        return $proceed();
    }

    /**
     * Get Review Entity Data
     *
     * @return array
     */
    private function getReviewEntityData(): array
    {
        $options = [];
        foreach ($this->reviewCollectionFactory->create() as $type) {
            $options[] = [
                'value' => $type->getEntityId(),
                'label' => $type->getEntityCode(),
            ];
        }
        return $options;
    }
}
