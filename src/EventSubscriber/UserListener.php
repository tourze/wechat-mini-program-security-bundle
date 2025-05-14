<?php

namespace WechatMiniProgramSecurityBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: UserInterface::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: UserInterface::class)]
class UserListener
{
    public function __construct(
        private readonly MediaSecurityService $mediaSecurityService,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function prePersist(UserInterface $entity, PrePersistEventArgs $eventArgs): void
    {
        if ($_ENV['DEFAULT_USER_AVATAR_URL'] !== $entity->getAvatarUrl()) {
            $this->check($entity, $entity->getAvatarUrl());
        }
    }

    public function preUpdate(UserInterface $entity, PreUpdateEventArgs $eventArgs): void
    {
        if ($_ENV['DEFAULT_USER_AVATAR_URL'] !== $entity->getAvatarUrl() && isset($eventArgs->getEntityChangeSet()['avatarUrl'])) {
            $this->check($entity, $entity->getAvatarUrl());
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
