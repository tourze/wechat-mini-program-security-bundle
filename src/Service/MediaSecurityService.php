<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\DoctrineDirectInsertBundle\Service\DirectInsertService;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;
use Yiisoft\Json\Json;

#[Autoconfigure(public: true)]
class MediaSecurityService
{
    public function __construct(
        private readonly MediaCheckRepository $mediaCheckRepository,
        private readonly DirectInsertService $directInsertService,
        private readonly Client $client,
    ) {
    }

    /**
     * 检查图片是否合法
     */
    #[Async]
    public function checkImage(UserInterface $wechatUser, string $url): void
    {
        $log = $this->mediaCheckRepository->findOneBy(['mediaUrl' => $url]);
        if ((bool) $log) {
            return;
        }

        $request = new MediaCheckAsyncRequest();
        // TODO: UserInterface does not have getAccount() method
        // $request->setAccount($wechatUser->getAccount());
        $request->setMediaUrl($url);
        $request->setMediaType(2);
        $request->setVersion(2);
        $request->setOpenId($wechatUser->getOpenId());
        $request->setScene(1);
        $res = $this->client->request($request);

        /** @var array<string, mixed>|null $res */
        if (null !== $res && isset($res['trace_id']) && is_string($res['trace_id'])) {
            $log = new MediaCheck();
            $log->setOpenId($wechatUser->getOpenId());
            $log->setUnionId($wechatUser->getUnionId());
            $log->setMediaUrl($url);
            $log->setTraceId($res['trace_id']);
            $log->setRawData(Json::encode($res));
            $this->directInsertService->directInsert($log);
        }
    }
}
