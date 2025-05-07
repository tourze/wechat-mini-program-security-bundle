<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Tourze\DoctrineAsyncBundle\Service\DoctrineService;
use Tourze\Symfony\Async\Attribute\Async;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;
use Yiisoft\Json\Json;

class MediaSecurityService
{
    public function __construct(
        private readonly MediaCheckRepository $mediaCheckRepository,
        private readonly DoctrineService $doctrineService,
        private readonly Client $client,
    ) {
    }

    /**
     * 检查图片是否合法
     */
    #[Async]
    public function checkImage(User $wechatUser, string $url): void
    {
        $log = $this->mediaCheckRepository->findOneBy(['mediaUrl' => $url]);
        if ($log) {
            return;
        }

        $request = new MediaCheckAsyncRequest();
        $request->setAccount($wechatUser->getAccount());
        $request->setMediaUrl($url);
        $request->setMediaType(2);
        $request->setVersion(2);
        $request->setOpenId($wechatUser->getOpenId());
        $request->setScene(1);
        $res = $this->client->request($request);
        if ($res && isset($res['trace_id'])) {
            $log = new MediaCheck();
            $log->setOpenId($wechatUser->getOpenId());
            $log->setUnionId($wechatUser->getUnionId());
            $log->setMediaUrl($url);
            $log->setTraceId($res['trace_id']);
            $log->setRawData(Json::encode($res));
            $this->doctrineService->directInsert($log);
        }
    }
}
