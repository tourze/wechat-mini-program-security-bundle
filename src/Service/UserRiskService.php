<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;
use WechatMiniProgramSecurityBundle\Request\GetUserRiskRankRequest;

class UserRiskService
{
    public function __construct(
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Async]
    public function checkWechatUser(UserInterface $user, int $scene, string $clientIp): void
    {
        $log = new RiskLog();
        $log->setUser($user);
        $log->setOpenId($user->getOpenId());
        $log->setUnionId($user->getUnionId());
        $log->setScene($scene);
        $log->setClientIp($clientIp);

        $request = new GetUserRiskRankRequest();
        $request->setAccount($log->getUser()->getAccount());
        $request->setOpenId($log->getUser()->getOpenId());
        $request->setScene($log->getScene());
        $request->setClientIp($log->getClientIp());
        if ($log->getUser()->getPhoneNumbers()->count() > 0) {
            $log->setMobileNo($log->getUser()->getPhoneNumbers()->first()->getPhoneNumber());
            $request->setMobileNumber($log->getMobileNo());
        }

        try {
            $response = $this->client->request($request);
        } catch (\Throwable $exception) {
            // 48001	小程序无该 api 权限
            if (48001 === $exception->getCode()) {
                $this->logger->warning('小程序无该 api 权限', [
                    'exception' => $exception,
                    'log' => $log,
                ]);
            }
            // 返回码为 61010，说明 openid 超时，目前传入的 openID 须在 30min 内有效访问小程序，否则会视为超时 openid。如果出现 61010 错误，需要用户用真机在小程序登录过才有效。
            if (61010 === $exception->getCode()) {
                $this->logger->warning('用户 openid 超时，需要用户用真机在小程序登录过才有效', [
                    'exception' => $exception,
                    'log' => $log,
                ]);
            }
            throw $exception;
        }

        $log->setRiskRank($response['risk_rank']);
        $log->setUnoinId(strval($response['unoin_id']));
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
