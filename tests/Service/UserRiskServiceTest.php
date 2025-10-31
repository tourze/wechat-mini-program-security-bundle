<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;

/**
 * @internal
 */
#[CoversClass(UserRiskService::class)]
#[RunTestsInSeparateProcesses]
final class UserRiskServiceTest extends AbstractIntegrationTestCase
{
    private UserRiskService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(UserRiskService::class);
    }

    public function testServiceInstantiation(): void
    {
        $this->assertInstanceOf(UserRiskService::class, $this->service);
    }

    public function testCheckWechatUserMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(UserRiskService::class);
        $this->assertTrue($reflectionClass->hasMethod('checkWechatUser'));

        $method = $reflectionClass->getMethod('checkWechatUser');
        $this->assertTrue($method->isPublic());
        $this->assertSame(3, $method->getNumberOfParameters());
    }
}
