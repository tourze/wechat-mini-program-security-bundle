<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use WechatMiniProgramSecurityBundle\EventSubscriber\RiskRankSubscriber;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;

#[CoversClass(RiskRankSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class RiskRankSubscriberTest extends AbstractEventSubscriberTestCase
{
    protected function onSetUp(): void
    {
        // Services are auto-registered
    }

    public function testRiskRankSubscriberIsRegisteredAsService(): void
    {
        $subscriber = self::getService(RiskRankSubscriber::class);
        $this->assertInstanceOf(RiskRankSubscriber::class, $subscriber);
    }

    public function testEventListenerAttributeIsPresent(): void
    {
        $reflectionClass = new \ReflectionClass(RiskRankSubscriber::class);
        $method = $reflectionClass->getMethod('onCodeToSessionResponse');
        $attributes = $method->getAttributes(AsEventListener::class);

        $this->assertNotEmpty($attributes, 'AsEventListener attribute should be present on onCodeToSessionResponse method');
    }

    public function testOnCodeToSessionResponseMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(RiskRankSubscriber::class);

        $this->assertTrue($reflectionClass->hasMethod('onCodeToSessionResponse'));
        $method = $reflectionClass->getMethod('onCodeToSessionResponse');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());
    }

    public function testSubscriberDependsOnUserRiskService(): void
    {
        $reflectionClass = new \ReflectionClass(RiskRankSubscriber::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('userRiskService', $parameters[0]->getName());
        $type = $parameters[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals(UserRiskService::class, $type->getName());
    }
}
