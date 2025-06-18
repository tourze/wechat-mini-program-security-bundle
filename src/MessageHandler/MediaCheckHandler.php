<?php

namespace WechatMiniProgramSecurityBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Message\MediaCheckMessage;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;
use Yiisoft\Json\Json;

#[AsMessageHandler]
class MediaCheckHandler
{
    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly MediaCheckRepository $mediaCheckRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(MediaCheckMessage $message): void
    {
        $wechatUser = $this->userLoader->loadUserByOpenId($message->getOpenId());
        if (!$wechatUser) {
            return;
        }
        if (!$wechatUser->getAccount()) {
            return;
        }
        if (!$message->getUrl()) {
            return;
        }

        $log = $this->mediaCheckRepository->findOneBy(['mediaUrl' => $message->getUrl()]);
        if ((bool) $log) {
            // 已经检查过了，没必要再继续
            return;
        }

        $request = new MediaCheckAsyncRequest();
        $request->setAccount($wechatUser->getAccount());
        $request->setMediaUrl($message->getUrl());
        $request->setMediaType(2);
        $request->setVersion(2);
        $request->setOpenId($wechatUser->getOpenId());
        $request->setScene(1);
        $res = $this->client->request($request);
        if ($res && (bool) isset($res['trace_id'])) {
            $log = new MediaCheck();
            $log->setOpenId($wechatUser->getOpenId());
            $log->setUnionId($wechatUser->getUnionId());
            $log->setMediaUrl($message->getUrl());
            $log->setTraceId($res['trace_id']);
            $log->setRawData(Json::encode($res));
            try {
                $this->entityManager->persist($log);
                $this->entityManager->flush();
            } catch (\Throwable $exception) {
                $this->logger->error('保存媒体检查日志出错', [
                    'log' => $log,
                    'exception' => $exception,
                ]);
            }
        }
    }
}
