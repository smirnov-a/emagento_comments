<?php

namespace Emagento\Comments\Api;

interface ReviewManagementInterface extends ManagementInterface
{
    /**
     * @api
     * @return string
     */
    public function getRaings();
}
