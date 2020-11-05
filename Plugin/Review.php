<?php

namespace Local\Comments\Plugin;

class Review
{
    private $logger;
    private $reviewFactory;

    /**
     * Review constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Review\Model\ReviewFactory $factory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Review\Model\ReviewFactory $factory
    ) {
        $this->logger = $logger;
        $this->reviewFactory = $factory;
    }

    /**
     * Работа с комментарием перед сохранением
     *
     * @param \Magento\Review\Model\Review $subject
     */
    public function beforeSave(\Magento\Review\Model\Review $subject)
    {
        $this->logger->info('plugin here. id: '.$subject->getId().'; entity_id: '.$subject->getEntityId());
        // перед сохранением нужно прописать поля 'path' и 'level'
        // они зависят от parent_id: если он не пустой, то сходить за данными parent'а я взять его level и path
        // только для комментариев к магазину
        if ($subject->getEntityId() == \Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE) {
            // значения по умолчанию
            $path = $subject->getId();
            $level = 1;
            if ($subject->getParentId()) {
                $parent = $this->reviewFactory->create()->load($subject->getParentId());
                if ($parent) {
                    // взять level и path
                    $level = $parent->getLevel() + 1;           // 4
                    $path = $parent->getPath() . '/' . $path;   // '3/4/5/6'
                }
            }
            $this->logger->info('Store Comment. Path: '.$path.'; level: '.$level);
            // прописать
            $subject
                ->setPath($path)
                ->setLevel($level);
        }
    }
}
