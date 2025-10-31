<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;
use WechatMiniProgramSecurityBundle\Request\GetUserRiskRankRequest;

#[WithMonologChannel(channel: 'wechat_mini_program_security')]
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
        $log = $this->createRiskLog($user, $scene, $clientIp);
        $request = $this->createRiskRequest($log);

        if (null === $request) {
            return;
        }

        try {
            $response = $this->client->request($request);
            $this->updateLogFromResponse($log, $response);
        } catch (\Throwable $exception) {
            $this->handleApiException($exception, $log);
            throw $exception;
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    private function createRiskLog(UserInterface $user, int $scene, string $clientIp): RiskLog
    {
        $log = new RiskLog();
        $log->setUser($user);
        $log->setOpenId($user->getOpenId());
        $log->setUnionId($user->getUnionId());
        $log->setScene($scene);
        $log->setClientIp($clientIp);

        return $log;
    }

    private function createRiskRequest(RiskLog $log): ?GetUserRiskRankRequest
    {
        $request = new GetUserRiskRankRequest();

        $openId = $log->getUser()?->getOpenId();
        if (null === $openId) {
            return null;
        }
        $request->setOpenId($openId);

        $scene = $log->getScene();
        if (null === $scene) {
            return null;
        }
        $request->setScene($scene);

        $clientIp = $log->getClientIp();
        if (null === $clientIp) {
            return null;
        }
        $request->setClientIp($clientIp);

        return $request;
    }

    private function updateLogFromResponse(RiskLog $log, mixed $response): void
    {
        if (!is_array($response)) {
            return;
        }

        $riskRank = $response['risk_rank'] ?? null;
        if (is_int($riskRank)) {
            $log->setRiskRank($riskRank);
        }

        $unoinId = $response['unoin_id'] ?? null;
        if (null !== $unoinId) {
            $log->setUnoinId(strval($unoinId));
        }
    }

    private function handleApiException(\Throwable $exception, RiskLog $log): void
    {
        $context = ['exception' => $exception, 'log' => $log];

        if (48001 === $exception->getCode()) {
            $this->logger->warning('小程序无该 api 权限', $context);
        }

        if (61010 === $exception->getCode()) {
            $this->logger->warning('用户 openid 超时，需要用户用真机在小程序登录过才有效', $context);
        }
    }
}
