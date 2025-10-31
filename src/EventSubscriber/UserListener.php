<?php

namespace WechatMiniProgramSecurityBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: UserInterface::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: UserInterface::class)]
#[WithMonologChannel(channel: 'wechat_mini_program_security')]
class UserListener
{
    public function __construct(
        private readonly MediaSecurityService $mediaSecurityService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function prePersist(UserInterface $entity, PrePersistEventArgs $eventArgs): void
    {
        $avatarUrl = $entity->getAvatarUrl();
        $defaultAvatarUrl = $_ENV['DEFAULT_USER_AVATAR_URL'] ?? null;
        if (null !== $avatarUrl && $defaultAvatarUrl !== $avatarUrl) {
            $this->check($entity, $avatarUrl);
        }
    }

    public function preUpdate(UserInterface $entity, PreUpdateEventArgs $eventArgs): void
    {
        $avatarUrl = $entity->getAvatarUrl();
        $defaultAvatarUrl = $_ENV['DEFAULT_USER_AVATAR_URL'] ?? null;
        if (null !== $avatarUrl && $defaultAvatarUrl !== $avatarUrl && isset($eventArgs->getEntityChangeSet()['avatarUrl'])) {
            $this->check($entity, $avatarUrl);
        }
    }

    private function check(UserInterface $user, string $url): void
    {
        // 进行多一次内容安全检测
        try {
            $this->mediaSecurityService->checkImage($user, $url);
        } catch (\Throwable $exception) {
            $this->logger->error('图片内容安全检测报错', [
                'url' => $url,
                'exception' => $exception,
            ]);
        }
    }
}
