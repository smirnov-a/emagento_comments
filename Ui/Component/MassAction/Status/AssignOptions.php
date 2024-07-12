<?php

namespace Emagento\Comments\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Laminas\Stdlib\JsonSerializable;
use Magento\Review\Helper\Data as ReviewHelper;

class AssignOptions implements JsonSerializable
{
    /** @var array */
    private $options;
    /** @var array */
    private $data;
    /** @var UrlInterface */
    private $urlBuilder;
    /** @var string */
    private $urlPath;
    /** @var string */
    private $paramName;
    /** @var array */
    private $additionalData = [];
    /** @var ReviewHelper */
    private $reviewHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param ReviewHelper $reviewHelper
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ReviewHelper $reviewHelper,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->reviewHelper = $reviewHelper;
        $this->data = $data;
    }

    /**
     * Get action options
     *
     * @return array|mixed
     */
    public function jsonSerialize(): mixed
    {
        if ($this->options === null) {
            $options = $this->reviewHelper->getReviewStatusesOptionArray();
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type'  => 'status_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }

                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }
            $this->options = array_values($this->options);
        }
        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
