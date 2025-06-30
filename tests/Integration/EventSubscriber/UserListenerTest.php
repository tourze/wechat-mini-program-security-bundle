<?php

namespace WechatMiniProgramSecurityBundle\Tests\Integration\EventSubscriber;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramSecurityBundle\EventSubscriber\UserListener;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

class UserListenerTest extends TestCase
{
    private MediaSecurityService|MockObject $mediaSecurityService;
    private LoggerInterface|MockObject $logger;
    private UserListener $listener;
    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        $this->mediaSecurityService = $this->createMock(MediaSecurityService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->listener = new UserListener(
            $this->mediaSecurityService,
            $this->logger
        );

        $_ENV['DEFAULT_USER_AVATAR_URL'] = 'https://example.com/default-avatar.jpg';
    }

    protected function tearDown(): void
    {
        unset($_ENV['DEFAULT_USER_AVATAR_URL']);
    }

    public function test_prePersist_withNonDefaultAvatar_shouldCheckImage(): void
    {
        $avatarUrl = 'https://example.com/user-avatar.jpg';
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->exactly(2))
            ->method('getAvatarUrl')
            ->willReturn($avatarUrl);

        $this->mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $avatarUrl);

        $eventArgs = new PrePersistEventArgs($user, $this->entityManager);
        
        $this->listener->prePersist($user, $eventArgs);
    }

    public function test_prePersist_withDefaultAvatar_shouldNotCheckImage(): void
    {
        $defaultAvatarUrl = $_ENV['DEFAULT_USER_AVATAR_URL'];
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())
            ->method('getAvatarUrl')
            ->willReturn($defaultAvatarUrl);

        $this->mediaSecurityService->expects($this->never())
            ->method('checkImage');

        $eventArgs = new PrePersistEventArgs($user, $this->entityManager);
        
        $this->listener->prePersist($user, $eventArgs);
    }

    public function test_preUpdate_withChangedAvatarUrl_shouldCheckImage(): void
    {
        $oldAvatarUrl = 'https://example.com/old-avatar.jpg';
        $newAvatarUrl = 'https://example.com/new-avatar.jpg';
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->exactly(2))
            ->method('getAvatarUrl')
            ->willReturn($newAvatarUrl);

        $changeSet = ['avatarUrl' => [$oldAvatarUrl, $newAvatarUrl]];
        
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet);

        $this->mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $newAvatarUrl);

        $this->listener->preUpdate($user, $eventArgs);
    }

    public function test_preUpdate_withoutAvatarUrlChange_shouldNotCheckImage(): void
    {
        $avatarUrl = 'https://example.com/user-avatar.jpg';
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())
            ->method('getAvatarUrl')
            ->willReturn($avatarUrl);

        $changeSet = ['name' => ['Old Name', 'New Name']];
        
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->once())
            ->method('getEntityChangeSet')
            ->willReturn($changeSet);

        $this->mediaSecurityService->expects($this->never())
            ->method('checkImage');

        $this->listener->preUpdate($user, $eventArgs);
    }

    public function test_preUpdate_withDefaultAvatarUrl_shouldNotCheckImage(): void
    {
        $defaultAvatarUrl = $_ENV['DEFAULT_USER_AVATAR_URL'];
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->once())
            ->method('getAvatarUrl')
            ->willReturn($defaultAvatarUrl);

        $changeSet = ['avatarUrl' => ['https://example.com/old.jpg', $defaultAvatarUrl]];
        
        $eventArgs = $this->createMock(PreUpdateEventArgs::class);
        $eventArgs->expects($this->never())
            ->method('getEntityChangeSet');

        $this->mediaSecurityService->expects($this->never())
            ->method('checkImage');

        $this->listener->preUpdate($user, $eventArgs);
    }

    public function test_check_withExceptionThrown_shouldLogError(): void
    {
        $avatarUrl = 'https://example.com/user-avatar.jpg';
        $exception = new \Exception('Network error');
        
        $user = $this->createMock(UserInterface::class);
        $user->expects($this->exactly(2))
            ->method('getAvatarUrl')
            ->willReturn($avatarUrl);

        $this->mediaSecurityService->expects($this->once())
            ->method('checkImage')
            ->with($user, $avatarUrl)
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                '图片内容安全检测报错',
                $this->callback(function ($context) use ($avatarUrl, $exception) {
                    return $context['url'] === $avatarUrl && $context['exception'] === $exception;
                })
            );

        $eventArgs = new PrePersistEventArgs($user, $this->entityManager);
        
        $this->listener->prePersist($user, $eventArgs);
    }

    public function test_doctrineEntityListenerAttributesArePresent(): void
    {
        $reflectionClass = new \ReflectionClass(UserListener::class);
        $attributes = $reflectionClass->getAttributes(\Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener::class);
        
        $this->assertCount(2, $attributes, 'UserListener should have 2 AsEntityListener attributes');
        
        $events = [];
        foreach ($attributes as $attribute) {
            $args = $attribute->getArguments();
            $events[] = $args['event'];
        }
        
        $this->assertContains(\Doctrine\ORM\Events::prePersist, $events);
        $this->assertContains(\Doctrine\ORM\Events::preUpdate, $events);
    }
}