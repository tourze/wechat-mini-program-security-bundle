<?php

namespace WechatMiniProgramSecurityBundle\Tests\Integration\EventSubscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;
use WechatMiniProgramSecurityBundle\EventSubscriber\RiskRankSubscriber;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;
use WechatMiniProgramSecurityBundle\Tests\Service\MockWechatUser;

class RiskRankSubscriberTest extends TestCase
{
    private UserRiskService|MockObject $userRiskService;
    private RiskRankSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->userRiskService = $this->createMock(UserRiskService::class);
        $this->subscriber = new RiskRankSubscriber($this->userRiskService);
    }

    public function test_onCodeToSessionResponse_shouldCallUserRiskServiceWithCorrectParameters(): void
    {
        $openId = 'test-open-id';
        $unionId = 'test-union-id';
        $clientIp = '192.168.1.100';

        $wechatUser = new MockWechatUser($openId, $unionId);
        
        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCreatedFromIp($clientIp);

        $event = new CodeToSessionResponseEvent();
        $event->setWechatUser($wechatUser);
        $event->setCodeSessionLog($codeSessionLog);

        $this->userRiskService->expects($this->once())
            ->method('checkWechatUser')
            ->with(
                $this->identicalTo($wechatUser),
                0,
                $clientIp
            );

        $this->subscriber->onCodeToSessionResponse($event);
    }

    public function test_onCodeToSessionResponse_withNullIp_shouldNotCallUserRiskService(): void
    {
        $openId = 'test-open-id';
        $unionId = 'test-union-id';

        $wechatUser = new MockWechatUser($openId, $unionId);
        
        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCreatedFromIp(null);

        $event = new CodeToSessionResponseEvent();
        $event->setWechatUser($wechatUser);
        $event->setCodeSessionLog($codeSessionLog);

        $this->userRiskService->expects($this->never())
            ->method('checkWechatUser');

        $this->subscriber->onCodeToSessionResponse($event);
    }

    public function test_eventListenerAttributeIsPresent(): void
    {
        $reflectionClass = new \ReflectionClass(RiskRankSubscriber::class);
        $method = $reflectionClass->getMethod('onCodeToSessionResponse');
        $attributes = $method->getAttributes(\Symfony\Component\EventDispatcher\Attribute\AsEventListener::class);
        
        $this->assertNotEmpty($attributes, 'AsEventListener attribute should be present on onCodeToSessionResponse method');
    }
}