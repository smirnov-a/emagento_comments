<?php

namespace Emagento\Comments\Api;

interface ReviewProviderInterface
{
    /**
     * Load and process Store Reviews
     *
     * @return int
     */
    public function loadAndProcessComments(): int;
}
