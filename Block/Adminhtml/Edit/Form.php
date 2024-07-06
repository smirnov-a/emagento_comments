<?php

namespace Emagento\Comments\Block\Adminhtml\Edit;

use Emagento\Comments\Block\Adminhtml\Rating\Detailed;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Review\Block\Adminhtml\Rating\Summary;
use Magento\Review\Helper\Data;
use Magento\Store\Model\System\Store;

class Form extends Generic
{
    /** @var Data|null */
    protected ?Data $reviewData = null;
    /** @var CustomerRepositoryInterface */
    protected CustomerRepositoryInterface $customerRepository;
    /** @var Store */
    protected Store $systemStore;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $reviewData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        CustomerRepositoryInterface $customerRepository,
        Data $reviewData,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->reviewData = $reviewData;
        $this->customerRepository = $customerRepository;
        $this->systemStore = $systemStore;
    }

    /**
     * Prepare Form
     *
     * @return $this
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): static
    {
        $review = $this->_coreRegistry->registry('review_data');

        $formActionParams = [
            'id'  => $this->getRequest()->getParam('id'),
            'ret' => $this->_coreRegistry->registry('ret')
        ];

        $form = $this->_formFactory->create([
            'data' => [
                'id'     => 'edit_form',
                'action' => $this->getUrl(
                    'local_comments/*/save',
                    $formActionParams
                ),
                'method' => 'post',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'review_details',
            ['legend' => __('Review Details'), 'class' => 'fieldset-wide']
        );

        try {
            $customer = $this->customerRepository->getById($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->_escaper->escapeHtml($customer->getFirstname()),
                $this->_escaper->escapeHtml($customer->getLastname()),
                $this->_escaper->escapeHtml($customer->getEmail())
            );
        } catch (NoSuchEntityException $e) {
            $customerText = ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                ? __('Administrator')
                : __('Guest');
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

        $fieldset->addField(
            'summary-rating',
            'note',
            [
                'label' => __('Summary Rating'),
                'text'  => $this->getLayout()->createBlock(
                    Summary::class
                )->toHtml(),
            ]
        );

        $fieldset->addField(
            'detailed-rating',
            'note',
            [
                'label'    => __('Detailed Rating'),
                'required' => true,
                'text'     => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    Detailed::class
                )->toHtml() . '</div>',
            ]
        );

        $fieldset->addField(
            'status_id',
            'select',
            [
                'label'    => __('Status'),
                'required' => true,
                'name'     => 'status_id',
                'values'   => $this->reviewData->getReviewStatusesOptionArray(),
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label'    => __('Visibility'),
                    'required' => true,
                    'name'     => 'stores[]',
                    'values'   => $this->systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $field->setRenderer($renderer);
            $review->setSelectStores($review->getStores());
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $review->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'nickname',
            'text',
            ['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
        );

        $fieldset->addField(
            'title',
            'text',
            ['label' => __('Summary of Review'), 'required' => true, 'name' => 'title']
        );

        $fieldset->addField(
            'detail',
            'textarea',
            ['label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:24em;']
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
