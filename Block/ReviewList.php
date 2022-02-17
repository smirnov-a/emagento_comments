<?php

namespace Emagento\Comments\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

/**
 * Class
 */
class ReviewList extends Template
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * ReviewList constructor.
     * @param Template\Context $context
     * @param array $data
     * @param Session $session
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $session;
    }

    /**
     * Return username (if not logged then from session)
     * @return string
     */
    public function getUserName()
    {
        $username = '';

        if ($this->_customerSession->isLoggedIn()) {
            $username = $this->_customerSession;
        } elseif ($this->_session->getReviewUserName()) {
            $username = $this->_session->getReviewUserName();
        }

        return $username;
    }
}
