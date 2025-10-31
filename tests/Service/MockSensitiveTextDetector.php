<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector;

/**
 * 用于测试的 SensitiveTextDetector 模拟实现
 */
class MockSensitiveTextDetector implements SensitiveTextDetector
{
    public function isSensitiveText(string $text, ?UserInterface $user = null): bool
    {
        // Always return false for testing
        return false;
    }
}
