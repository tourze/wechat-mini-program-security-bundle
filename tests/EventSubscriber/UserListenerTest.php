<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\EventSubscriber\UserListener;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

/**
 * @internal
 */
#[CoversClass(UserListener::class)]
#[RunTestsInSeparateProcesses]
final class UserListenerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Services are auto-registered
    }

    public function testUserListenerIsRegisteredAsService(): void
    {
        $listener = self::getService(UserListener::class);
        $this->assertInstanceOf(UserListener::class, $listener);
    }

    public function testDoctrineEntityListenerAttributesArePresent(): void
    {
        $reflectionClass = new \ReflectionClass(UserListener::class);
        $attributes = $reflectionClass->getAttributes(AsEntityListener::class);

        $this->assertCount(2, $attributes, 'UserListener should have 2 AsEntityListener attributes');

        $events = [];
        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            $events[] = $args['event'];
        }

        $this->assertContains(Events::prePersist, $events);
        $this->assertContains(Events::preUpdate, $events);
    }

    public function testPrePersistMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(UserListener::class);

        $this->assertTrue($reflectionClass->hasMethod('prePersist'));
        $method = $reflectionClass->getMethod('prePersist');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
    }

    public function testPreUpdateMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(UserListener::class);

        $this->assertTrue($reflectionClass->hasMethod('preUpdate'));
        $method = $reflectionClass->getMethod('preUpdate');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
    }

    public function testListenerDependsOnMediaSecurityService(): void
    {
        $reflectionClass = new \ReflectionClass(UserListener::class);
        $constructor = $reflectionClass->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();

        $this->assertGreaterThanOrEqual(1, count($parameters));
        $this->assertEquals('mediaSecurityService', $parameters[0]->getName());
        $type = $parameters[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals(MediaSecurityService::class, $type->getName());
    }
}
