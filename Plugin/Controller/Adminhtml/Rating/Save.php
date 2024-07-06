<?php

namespace Emagento\Comments\Plugin\Controller\Adminhtml\Rating;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Rating\OptionFactory;
use Magento\Backend\Model\SessionFactory;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Review::ratings';

    /** @var Registry */
    protected Registry $coreRegistry;
    /** @var RatingFactory */
    private RatingFactory $ratingFactory;
    /** @var OptionFactory */
    private OptionFactory $optionFactory;
    /** @var SessionFactory */
    private SessionFactory $sessionFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RatingFactory $ratingFactory
     * @param OptionFactory $optionFactory
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RatingFactory $ratingFactory,
        OptionFactory $optionFactory,
        SessionFactory $sessionFactory,
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->ratingFactory = $ratingFactory;
        $this->optionFactory = $optionFactory;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * Around Execute
     *
     * @param \Magento\Review\Controller\Adminhtml\Rating\Save $subject
     * @param \Closure $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function aroundExecute(\Magento\Review\Controller\Adminhtml\Rating\Save $subject, \Closure $proceed)
    {
        return $this->execute();
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getPostValue()) {
            try {
                $ratingModel = $this->ratingFactory->create();
                $stores = $this->getRequest()->getParam('stores');
                $position = (int) $this->getRequest()->getParam('position');
                $stores[] = 0;
                $isActive = (bool) $this->getRequest()->getParam('is_active');

                $ratingModel->setRatingCode($this->getRequest()->getParam('rating_code'))
                    ->setRatingCodes($this->getRequest()->getParam('rating_codes'))
                    ->setStores($stores)
                    ->setPosition($position)
                    ->setId($this->getRequest()->getParam('id'))
                    ->setIsActive($isActive)
                    ->setEntityId($this->getRequest()->getParam('entity_id'))
                    ->save();

                $options = $this->getRequest()->getParam('option_title');

                if (is_array($options)) {
                    $i = 1;
                    foreach ($options as $key => $optionCode) {
                        $optionModel = $this->optionFactory->create();
                        if (!preg_match("/^add_([0-9]*?)$/", $key)) {
                            $optionModel->setId($key);
                        }

                        $optionModel->setCode($optionCode)
                            ->setValue($i)
                            ->setRatingId($ratingModel->getId())
                            ->setPosition($i)
                            ->save();
                        $i++;
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved the rating.'));
                $this->sessionFactory->create()
                    ->setRatingData(false);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->sessionFactory->create()
                    ->setRatingData($this->getRequest()->getPostValue());
                $resultRedirect->setPath('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('review/rating/');

        return $resultRedirect;
    }
}
