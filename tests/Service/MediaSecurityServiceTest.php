<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

/**
 * @internal
 */
#[CoversClass(MediaSecurityService::class)]
#[RunTestsInSeparateProcesses]
final class MediaSecurityServiceTest extends AbstractIntegrationTestCase
{
    private MediaSecurityService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(MediaSecurityService::class);
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(MediaSecurityService::class, $this->service);
    }

    public function testCheckImageMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(MediaSecurityService::class);
        $this->assertTrue($reflectionClass->hasMethod('checkImage'));

        $method = $reflectionClass->getMethod('checkImage');
        $this->assertTrue($method->isPublic());
        $this->assertSame(2, $method->getNumberOfParameters());
    }
}
