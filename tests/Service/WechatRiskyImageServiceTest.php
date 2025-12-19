<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\Service\WechatRiskyImageService;

/**
 * @internal
 */
#[CoversClass(WechatRiskyImageService::class)]
#[RunTestsInSeparateProcesses]
final class WechatRiskyImageServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // No setup required for this test
    }

    public function testServiceInstantiation(): void
    {
        $service = self::getService(WechatRiskyImageService::class);
        $this->assertInstanceOf(WechatRiskyImageService::class, $service);
    }

    public function testIsRiskyImageMethodExists(): void
    {
        $service = self::getService(WechatRiskyImageService::class);
        $reflectionClass = new \ReflectionClass($service);
        $this->assertTrue($reflectionClass->hasMethod('isRiskyImage'));

        $method = $reflectionClass->getMethod('isRiskyImage');
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());
    }

    public function testIsRiskyImageWithNoData(): void
    {
        $service = self::getService(WechatRiskyImageService::class);
        $result = $service->isRiskyImage('test-image-url.jpg');
        $this->assertFalse($result);
    }
}
