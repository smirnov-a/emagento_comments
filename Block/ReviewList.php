<?php

namespace Emagento\Comments\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class ReviewList extends Template
{
    /** @var Session */
    private Session $customerSession;

    /**
     * @param Template\Context $context
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $session;
    }

    /**
     * Get Username
     *
     * @return string
     */
    public function getUserName(): string
    {
        $username = '';

        if ($this->customerSession->isLoggedIn()) {
            $username = $this->customerSession;
        } elseif ($this->_session->getReviewUserName()) {
            $username = $this->_session->getReviewUserName();
        }

        return $username;
    }
}
