<?php

namespace Local\Comments\Block;

/**
 * Class
 */
class ReviewList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * ReviewList constructor.
     * @param \Magento\Framework\View\Element\Template\Context $contex
     * @param array $data
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $session;
    }

    /**
     * Возвращает имя пользователя (если не залогинен, то из сессии)
     * @return string
     */
    public function getUserName()
    {
        $username = '';
        //$data = $this->_session->getData(); var_dump($data); exit;
        // сперва попробовать взять с клиента
        if ($this->_customerSession->isLoggedIn()) {
            $username = $this->_customerSession;
        } elseif ($this->_session->getReviewUserName()) {
            // иначе попробоавть из сессии (туда попадет из контроллера при сохранении отзыва)
            $username = $this->_session->getReviewUserName();
        }

        return $username;
    }
}
