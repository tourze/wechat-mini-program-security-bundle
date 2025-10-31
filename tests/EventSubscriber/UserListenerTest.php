<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
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
        // Services are auto-registered, no need to set up anything here
    }

    /**
     * @return array{MediaSecurityService, LoggerInterface, EntityManagerInterface, UserListener}
     */
    private function createTestEnvironment(): array
    {
        // 创建 Mock 依赖
        $mediaSecurityService = $this->createMock(MediaSecurityService::class);
        $logger = $this->createMock(LoggerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        // 直接创建被测试的服务实例
        // 由于服务容器限制，无法在测试中替换已初始化的服务
        // @phpstan-ignore integrationTest.noDirectInstantiationOfCoveredClass
        $listener = new UserListener($mediaSecurityService, $logger);

        return [$mediaSecurityService, $logger, $entityManager, $listener];
    }

    private function createWechatUserMock(string $avatarUrl = 'https://example.com/default.jpg'): UserInterface
    {
        $miniProgram = $this->createMock(MiniProgramInterface::class);

        $user = $this->createMock(UserInterface::class);
        $user->method('getAvatarUrl')->willReturn($avatarUrl);
        $user->method('getOpenId')->willReturn('test-openid');
        $user->method('getUnionId')->willReturn(null);
        $user->method('getMiniProgram')->willReturn($miniProgram);

        return $user;
    }

    public function testPrePersistWithNonDefaultAvatarShouldCheckImage(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $avatarUrl = 'https://example.com/user-avatar.jpg';

        $user = $this->createWechatUserMock($avatarUrl);

        $mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $avatarUrl)
        ;

        $eventArgs = new PrePersistEventArgs($user, $entityManager);

        // 执行方法并验证checkImage被调用
        $listener->prePersist($user, $eventArgs);
    }

    public function testPrePersistWithDefaultAvatarShouldNotCheckImage(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $defaultAvatarUrl = 'https://example.com/default-avatar.jpg';
        $_ENV['DEFAULT_USER_AVATAR_URL'] = $defaultAvatarUrl;

        $user = $this->createWechatUserMock($defaultAvatarUrl);

        $mediaSecurityService->expects($this->never())
            ->method('checkImage')
        ;

        $eventArgs = new PrePersistEventArgs($user, $entityManager);

        $listener->prePersist($user, $eventArgs);

        unset($_ENV['DEFAULT_USER_AVATAR_URL']);
    }

    public function testPreUpdateWithChangedAvatarUrlShouldCheckImage(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $oldAvatarUrl = 'https://example.com/old-avatar.jpg';
        $newAvatarUrl = 'https://example.com/new-avatar.jpg';

        $user = $this->createWechatUserMock($newAvatarUrl);

        $changeSet = ['avatarUrl' => [$oldAvatarUrl, $newAvatarUrl]];

        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet)
        ;

        $mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $newAvatarUrl)
        ;

        // 执行方法并验证checkImage被调用
        $listener->preUpdate($user, $eventArgs);
    }

    public function testPreUpdateWithoutAvatarUrlChangeShouldNotCheckImage(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $avatarUrl = 'https://example.com/user-avatar.jpg';

        $user = $this->createWechatUserMock($avatarUrl);

        $changeSet = ['name' => ['Old Name', 'New Name']];

        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet)
        ;

        $mediaSecurityService->expects($this->never())
            ->method('checkImage')
        ;

        // 执行方法并验证没有调用checkImage
        $listener->preUpdate($user, $eventArgs);
    }

    public function testPreUpdateWithDefaultAvatarUrlShouldNotCheckImage(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $defaultAvatarUrl = 'https://example.com/default-avatar.jpg';
        $_ENV['DEFAULT_USER_AVATAR_URL'] = $defaultAvatarUrl;

        $user = $this->createWechatUserMock($defaultAvatarUrl);

        $changeSet = ['avatarUrl' => ['https://example.com/old.jpg', $defaultAvatarUrl]];

        // 因为avatarUrl是默认URL，条件会短路，不会调用getEntityChangeSet()
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->never())
            ->method('getEntityChangeSet')
        ;

        $mediaSecurityService->expects($this->never())
            ->method('checkImage')
        ;

        $listener->preUpdate($user, $eventArgs);

        unset($_ENV['DEFAULT_USER_AVATAR_URL']);
    }

    public function testCheckWithExceptionThrownShouldLogError(): void
    {
        [$mediaSecurityService, $logger, $entityManager, $listener] = $this->createTestEnvironment();

        $avatarUrl = 'https://example.com/user-avatar.jpg';
        $exception = new \Exception('Network error');

        $user = $this->createWechatUserMock($avatarUrl);

        $mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $avatarUrl)
            ->willThrowException($exception)
        ;

        $logger->expects($this->once())
            ->method('error')
            ->with(
                '图片内容安全检测报错',
                self::callback(function ($context) use ($avatarUrl, $exception) {
                    return $context['url'] === $avatarUrl && $context['exception'] === $exception;
                })
            )
        ;

        $eventArgs = new PrePersistEventArgs($user, $entityManager);

        // 执行方法并验证异常被捕获和日志被记录
        $listener->prePersist($user, $eventArgs);
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
}
