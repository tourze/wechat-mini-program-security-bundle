<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Tourze\RiskyImageDetectBundle\Service\RiskyImageDetector;

/**
 * Mock implementation of RiskyImageDetector for testing
 */
class MockRiskyImageDetector implements RiskyImageDetector
{
    public function isRiskyImage(string $image): bool
    {
        // Always return false for testing
        return false;
    }
}
