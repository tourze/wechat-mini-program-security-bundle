<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\Service\SensitiveTextService;

/**
 * @internal
 */
#[CoversClass(SensitiveTextService::class)]
#[RunTestsInSeparateProcesses]
final class SensitiveTextServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Mock the external dependency
        self::getContainer()->set('Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector', new MockSensitiveTextDetector());
    }

    public function testServiceInstantiation(): void
    {
        $service = self::getService(SensitiveTextService::class);
        $this->assertInstanceOf(SensitiveTextService::class, $service);
    }

    public function testIsSensitiveTextMethodExists(): void
    {
        $service = self::getService(SensitiveTextService::class);
        $reflectionClass = new \ReflectionClass($service);
        $this->assertTrue($reflectionClass->hasMethod('isSensitiveText'));

        $method = $reflectionClass->getMethod('isSensitiveText');
        $this->assertTrue($method->isPublic());
        $this->assertSame(2, $method->getNumberOfParameters());
    }

    public function testCheckSensitiveTextMethodExists(): void
    {
        $service = self::getService(SensitiveTextService::class);
        $reflectionClass = new \ReflectionClass($service);
        $this->assertTrue($reflectionClass->hasMethod('checkSensitiveText'));

        $method = $reflectionClass->getMethod('checkSensitiveText');
        $this->assertTrue($method->isPublic());
        $this->assertSame(2, $method->getNumberOfParameters());
    }

    public function testCheckSensitiveTextWithNoInnerService(): void
    {
        $service = self::getService(SensitiveTextService::class);
        $result = $service->checkSensitiveText('test text');
        $this->assertFalse($result);
    }
}
