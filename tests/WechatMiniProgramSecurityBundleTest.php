<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use WechatMiniProgramSecurityBundle\WechatMiniProgramSecurityBundle;


#[CoversClass(WechatMiniProgramSecurityBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramSecurityBundleTest extends AbstractBundleTestCase
{
    protected function onSetUp(): void
    {
        // Bundle 测试不需要额外设置
    }
}
