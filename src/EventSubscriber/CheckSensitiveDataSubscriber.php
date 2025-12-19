<?php

namespace WechatMiniProgramSecurityBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Json\Json;

/**
 * 敏感词检查，使用微信接口也检查一次咯
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.msgSecCheck.html
 */
#[WithMonologChannel(channel: 'wechat_mini_program_security')]
final class CheckSensitiveDataSubscriber
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MediaCheckRepository $mediaCheckRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        // private readonly MessageBusInterface $messageBus,
    ) {
    }

    //    /**
    //     * 检查图片内容
    //     *
    //     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.mediaCheckAsync.html
    //     */
    //    #[AsEventListener]
    //    public function onAfterFileUpload(AfterFileUploadEvent $event): void
    //    {
    //        if (empty($event->getUrl())) {
    //            return;
    //        }
    //
    //        $openId = $event->getRequest()->request->get('openId');
    //        if (!$openId) {
    //            return;
    //        }
    //
    //        $message = new MediaCheckMessage();
    //        $message->setOpenId($openId);
    //        $message->setUrl($event->getUrl());
    //        $this->messageBus->dispatch($message);
    //    }

    /**
     * 收到服务端消息回调时，同步处理结果到数据库
     */
    #[AsEventListener]
    public function onServerMessage(ServerMessageRequestEvent $event): void
    {
        /** @var array<string, mixed> $message */
        $message = $event->getMessage();

        // 旧版本的接口，会返回 isrisky
        if ($this->isLegacyMessage($message)) {
            $this->handleLegacyMessage($message);

            return;
        }

        // 中间版本的接口，result.suggest
        if ($this->isMiddleVersionMessage($message)) {
            $this->handleMiddleVersionMessage($message);

            return;
        }

        // 新版本返回值，detail.suggest
        if ($this->isNewVersionMessage($message)) {
            $this->handleNewVersionMessage($message);

            return;
        }
    }

    /**
     * @param array<string, mixed> $message
     */
    private function isLegacyMessage(array $message): bool
    {
        return isset($message['isrisky']) && isset($message['trace_id']);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function isMiddleVersionMessage(array $message): bool
    {
        return isset($message['trace_id'])
            && isset($message['result'])
            && is_array($message['result'])
            && isset($message['result']['suggest']);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function isNewVersionMessage(array $message): bool
    {
        return isset($message['trace_id']) && isset($message['detail']);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleLegacyMessage(array $message): void
    {
        if (!isset($message['trace_id']) || !is_string($message['trace_id'])) {
            return;
        }

        $log = $this->findMediaCheckLog($message['trace_id']);
        if (null === $log) {
            return;
        }

        $log->setRawData(Json::encode($message));
        $log->setRisky((bool) $message['isrisky']);
        $this->saveMediaCheckLog($log, '旧版本更新媒体检查日志出错');
        $this->dispatchMediaCheckEvent($log);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleMiddleVersionMessage(array $message): void
    {
        if (!isset($message['trace_id']) || !is_string($message['trace_id'])) {
            return;
        }

        $log = $this->findMediaCheckLog($message['trace_id']);
        if (null === $log) {
            return;
        }

        $log->setRawData(Json::encode($message));

        $suggest = 'pass';
        if (isset($message['result']) && is_array($message['result']) && isset($message['result']['suggest']) && is_string($message['result']['suggest'])) {
            $suggest = $message['result']['suggest'];
        }
        $log->setRisky('risky' === $suggest);

        $this->saveMediaCheckLog($log, '中间版本更新媒体检查日志出错');
        $this->dispatchMediaCheckEvent($log);
    }

    /**
     * @param array<string, mixed> $message
     */
    private function handleNewVersionMessage(array $message): void
    {
        if (!isset($message['trace_id']) || !is_string($message['trace_id'])) {
            return;
        }

        $log = $this->findMediaCheckLog($message['trace_id']);
        if (null === $log) {
            return;
        }

        $log->setRawData(Json::encode($message));

        $suggest = 'pass';
        if (isset($message['detail']) && is_array($message['detail']) && isset($message['detail']['suggest']) && is_string($message['detail']['suggest'])) {
            $suggest = $message['detail']['suggest'];
        }
        $log->setRisky('pass' !== $suggest);

        $this->saveMediaCheckLog($log, '新版本更新媒体检查日志出错');
        $this->dispatchMediaCheckEvent($log);
    }

    private function findMediaCheckLog(string $traceId): ?MediaCheck
    {
        return $this->mediaCheckRepository->findOneBy(['traceId' => $traceId]);
    }

    private function saveMediaCheckLog(MediaCheck $log, string $errorMessage): void
    {
        try {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error($errorMessage, [
                'log' => $log,
                'exception' => $exception,
            ]);
        }
    }

    private function dispatchMediaCheckEvent(MediaCheck $log): void
    {
        $event = new MediaCheckAsyncEvent();
        $event->setMediaCheckLog($log);
        $this->eventDispatcher->dispatch($event);
    }
}
