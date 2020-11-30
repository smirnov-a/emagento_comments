<?php

namespace Emagento\Comments\Model\Config;

class Rating implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $_ratingFactory;

    /**
     * Rating constructor.
     * @param \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingFactory
     */
    public function __construct(\Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingFactory)
    {
        $this->_ratingFactory = $ratingFactory;
    }

    /**
     * Return list of ratings as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        /** @var \Magento\Review\Model\ResourceModel\Rating\Collection */
        $ratingCollection = $this->_ratingFactory->create();
        $ratingCollection->addEntityFilter(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE);
        //echo $ratingCollection->getSelect(); exit;
        foreach ($ratingCollection as $rating) {
            //echo $rating->getRatingId().' '.$rating->getRatingCode()."<br/>";
            $result[] = [
                'value' => $rating->getRatingId(),
                'label' => $rating->getRatingCode()
            ];
        }
        //var_dump($result); exit;

        return $result;
    }
}
