<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Emagento\Comments\Helper\Data as DataSource;

class Source extends Column implements OptionSourceInterface
{
    /** @var DataSource */
    public DataSource $source;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DataSource $source
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DataSource $source,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->source = $source;
    }

    /**
     * Get Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->source->getSourceOptionArray();
    }
}
