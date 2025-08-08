<?php

declare(strict_types=1);

namespace app\services\Strategy;

/**
 * CheckSiteStrategyInterface is a contract for different site checking strategies.
 */
interface CheckSiteStrategyInterface
{
    /**
     * Load target data from a given URL.
     * @param string $url
     * @return string
     */
    public function loadTargetData(string $url): string;
}