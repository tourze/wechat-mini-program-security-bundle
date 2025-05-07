<?php

namespace WechatMiniProgramSecurityBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use FileSystemBundle\Event\AfterFileUploadEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;
use WechatMiniProgramSecurityBundle\Message\MediaCheckMessage;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Json\Json;

/**
 * 敏感词检查，使用微信接口也检查一次咯
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.msgSecCheck.html
 */
class CheckSensitiveDataSubscriber
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MediaCheckRepository $mediaCheckRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 检查图片内容
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.mediaCheckAsync.html
     */
    #[AsEventListener]
    public function onAfterFileUpload(AfterFileUploadEvent $event): void
    {
        if (empty($event->getUrl())) {
            return;
        }

        $openId = $event->getRequest()->request->get('openId');
        if (!$openId) {
            return;
        }

        $message = new MediaCheckMessage();
        $message->setOpenId($openId);
        $message->setUrl($event->getUrl());
        $this->messageBus->dispatch($message);
    }

    /**
     * 收到服务端消息回调时，同步处理结果到数据库
     */
    #[AsEventListener]
    public function onServerMessage(ServerMessageRequestEvent $event): void
    {
        $message = $event->getMessage();

        // 旧版本的接口，会返回 isrisky
        if (isset($message['isrisky']) && isset($message['trace_id'])) {
            $log = $this->mediaCheckRepository->findOneBy([
                'traceId' => $message['trace_id'],
            ]);
            if (!$log) {
                return;
            }

            $log->setRawData(Json::encode($message));
            $log->setRisky((bool) $message['isrisky']);
            try {
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error('旧版本更新媒体检查日志出错', [
                    'log' => $log,
                    'exception' => $exception,
                ]);
            }

            $event = new MediaCheckAsyncEvent();
            $event->setMediaCheckLog($log);
            $this->eventDispatcher->dispatch($event);

            return;
        }

        // {
        //    "ToUserName": "gh_6b8d87e0a0bd",
        //    "FromUserName": "oEAYS5cJa2e80i290W3OlqpT45Z0",
        //    "CreateTime": 1671441765,
        //    "MsgType": "event",
        //    "Event": "wxa_media_check",
        //    "appid": "wx9788481f42e6b49a",
        //    "trace_id": "63a02d61-46ea413c-62bccfbb",
        //    "version": 2,
        //    "detail": [
        //        {
        //            "strategy": "content_model",
        //            "errcode": 0,
        //            "suggest": "risky",
        //            "label": 20002,
        //            "prob": 90
        //        }
        //    ],
        //    "errcode": 0,
        //    "errmsg": "ok",
        //    "result": {
        //        "suggest": "risky",
        //        "label": 20002
        //    }
        // }
        if (isset($message['trace_id']) && isset($message['result']) && isset($message['result']['suggest'])) {
            $log = $this->mediaCheckRepository->findOneBy([
                'traceId' => $message['trace_id'],
            ]);
            if (!$log) {
                return;
            }

            $log->setRawData(Json::encode($message));
            $log->setRisky('risky' === $message['result']['suggest']);
            try {
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error('旧版本更新媒体检查日志出错', [
                    'log' => $log,
                    'exception' => $exception,
                ]);
            }

            $event = new MediaCheckAsyncEvent();
            $event->setMediaCheckLog($log);
            $this->eventDispatcher->dispatch($event);

            return;
        }

        // 新版本返回值：
        // [▼
        //  "ToUserName" => "gh_262ad44747a1"
        //  "FromUserName" => "ovGLy5IoHjvDttxvKrYKjnUuUdAw"
        //  "CreateTime" => "1659115626"
        //  "MsgType" => "event"
        //  "Event" => "wxa_media_check"
        //  "appid" => "wx0a12393911b1f4ff"
        //  "trace_id" => "62e41866-0d622bf2-19fc0080"
        //  "version" => "2"
        //  "detail" => [▼
        //    "strategy" => "content_model"
        //    "errcode" => "0"
        //    "suggest" => "pass"
        //    "label" => "100"
        //    "prob" => "90"
        //  ]
        //  "errcode" => "0"
        //  "errmsg" => "ok"
        //  "result" => [▼
        //    "suggest" => "pass"
        //    "label" => "100"
        //  ]
        // ]
        if (isset($message['trace_id']) && isset($message['detail'])) {
            $log = $this->mediaCheckRepository->findOneBy([
                'traceId' => $message['trace_id'],
            ]);
            if (!$log) {
                return;
            }

            $log->setRawData(Json::encode($message));
            $log->setRisky('pass' !== $message['detail']['suggest']);
            try {
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error('新版本更新媒体检查日志出错', [
                    'log' => $log,
                    'exception' => $exception,
                ]);
            }
            $event = new MediaCheckAsyncEvent();
            $event->setMediaCheckLog($log);
            $this->eventDispatcher->dispatch($event);

            return;
        }
    }
}
