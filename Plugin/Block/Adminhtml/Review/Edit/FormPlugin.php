<?php

namespace Emagento\Comments\Plugin\Block\Adminhtml\Review\Edit;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Review\Block\Adminhtml\Edit\Form;
use Emagento\Comments\Model\ResourceModel\Review\Entity\CollectionFactory as ReviewEntityCollection;
use Magento\Framework\Registry;
use Emagento\Comments\Helper\Constants;

class FormPlugin
{
    /** @var ReviewEntityCollection */
    protected ReviewEntityCollection $reviewEntityCollectionFactory;
    /** @var Registry */
    protected Registry $registry;
    /** @var RequestInterface */
    protected RequestInterface $request;

    /**
     * @param ReviewEntityCollection $reviewCollection
     * @param Registry $registry
     * @param RequestInterface $request
     */
    public function __construct(
        ReviewEntityCollection $reviewCollection,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->reviewEntityCollectionFactory = $reviewCollection;
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * After Get Form
     *
     * @param Form $subject
     * @param mixed $result
     * @return \Magento\Framework\Data\Form|mixed
     */
    public function afterGetForm(
        Form $subject,
        $result
    ) {
        if ($result instanceof \Magento\Framework\Data\Form) {
            $fieldset = $result->getForm()->getElement('review_details');
            if ($fieldset && !$this->isFieldExists($fieldset, 'entity_id')) {
                $review = $this->registry->registry('review_data');
                $value = $review ? $review->getEntityId() : null;
                $fieldset->addField(
                    'entity_id',
                    'select',
                    [
                        'name'     => 'entity_id',
                        'label'    => __('Entity Type'),
                        'title'    => __('Entity Type'),
                        'required' => true,
                        'value'    => $value,
                        'class'    => 'required-entry',
                        'values'   => $this->getReviewEntityData(),
                        'disabled' => !$this->isRequestByLocalCommentEditForm(),
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * Get Review Entity Data
     *
     * @return array
     */
    private function getReviewEntityData(): array
    {
        $options = [];
        $collection = $this->reviewEntityCollectionFactory->create();
        foreach ($collection->getItems() as $type) {
            $options[] = [
                'value' => $type->getEntityId(),
                'label' => $type->getEntityCode(),
            ];
        }

        return $options;
    }

    /**
     * Check if Field exists
     *
     * @param AbstractElement $fieldset
     * @param string $fieldName
     * @return bool
     */
    private function isFieldExists(AbstractElement $fieldset, string $fieldName): bool
    {
        foreach ($fieldset->getElements() as $element) {
            if ($element->getId() === $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if Request Path is Local Edit Form
     *
     * @return bool
     */
    private function isRequestByLocalCommentEditForm(): bool
    {
        $pos = strpos($this->request->getPathInfo(), Constants::LOCAL_COMMENT_EDIT_PATH);
        return $pos !== false;
    }
}
