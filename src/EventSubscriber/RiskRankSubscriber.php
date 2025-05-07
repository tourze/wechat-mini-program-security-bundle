<?php

namespace WechatMiniProgramSecurityBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;

/**
 * 当用户进行某些操作时，我们对其进行一些安全检测
 */
class RiskRankSubscriber
{
    public function __construct(private readonly UserRiskService $userRiskService)
    {
    }

    #[AsEventListener]
    public function onCodeToSessionResponse(CodeToSessionResponseEvent $event): void
    {
        $this->userRiskService->checkWechatUser(
            $event->getWechatUser(),
            0,
            $event->getCodeSessionLog()->getCreatedFromIp(),
        );
    }
}
