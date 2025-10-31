<?php

namespace WechatMiniProgramSecurityBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\MessageHandler\MediaCheckHandler;

/**
 * @internal
 */
#[CoversClass(MediaCheckHandler::class)]
#[RunTestsInSeparateProcesses]
final class MediaCheckHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Services are auto-registered, no need to set up anything here
    }

    public function testInvokeMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(MediaCheckHandler::class);
        $this->assertTrue($reflectionClass->hasMethod('__invoke'));

        $method = $reflectionClass->getMethod('__invoke');
        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());
    }
}
