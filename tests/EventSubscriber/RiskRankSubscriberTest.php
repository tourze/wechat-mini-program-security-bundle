<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;
use WechatMiniProgramSecurityBundle\EventSubscriber\RiskRankSubscriber;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;
use WechatMiniProgramSecurityBundle\Tests\Service\MockWechatUser;

/**
 * @internal
 * @phpstan-ignore symplify.forbiddenExtendOfNonAbstractClass,eventSubscriberTest.mustInheritAbstractIntegrationTestCase
 */
#[CoversClass(RiskRankSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class RiskRankSubscriberTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Services are auto-registered, no need to set up anything here
    }

    /**
     * @return array{UserRiskService, RiskRankSubscriber}
     */
    private function createSubscriber(): array
    {
        // 必须使用具体类 UserRiskService 的 Mock
        // 1. 该测试需要验证 Service 的具体方法调用行为
        // 2. UserRiskService 没有对应的 interface 可以使用
        // 3. 测试的核心逻辑就是验证 Service 的方法是否被正确调用
        $userRiskService = $this->createMock(UserRiskService::class);

        // 将 Mock 服务注册到容器中
        self::getContainer()->set(UserRiskService::class, $userRiskService);

        // 从容器获取被测试的服务实例
        $subscriber = self::getService(RiskRankSubscriber::class);

        return [$userRiskService, $subscriber];
    }

    public function testOnCodeToSessionResponseShouldCallUserRiskServiceWithCorrectParameters(): void
    {
        [$userRiskService, $subscriber] = $this->createSubscriber();

        $openId = 'test-open-id';
        $unionId = 'test-union-id';
        $clientIp = '192.168.1.100';

        $wechatUser = new MockWechatUser($openId, $unionId);

        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCreatedFromIp($clientIp);

        $event = new CodeToSessionResponseEvent();
        $event->setWechatUser($wechatUser);
        $event->setCodeSessionLog($codeSessionLog);

        $userRiskService->expects($this->once())
            ->method('checkWechatUser')
            ->with(
                self::identicalTo($wechatUser),
                0,
                $clientIp
            )
        ;

        $subscriber->onCodeToSessionResponse($event);
    }

    public function testOnCodeToSessionResponseWithNullIpShouldNotCallUserRiskService(): void
    {
        [$userRiskService, $subscriber] = $this->createSubscriber();

        $openId = 'test-open-id';
        $unionId = 'test-union-id';

        $wechatUser = new MockWechatUser($openId, $unionId);

        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCreatedFromIp(null);

        $event = new CodeToSessionResponseEvent();
        $event->setWechatUser($wechatUser);
        $event->setCodeSessionLog($codeSessionLog);

        $userRiskService->expects($this->never())
            ->method('checkWechatUser')
        ;

        $subscriber->onCodeToSessionResponse($event);
    }

    public function testEventListenerAttributeIsPresent(): void
    {
        $reflectionClass = new \ReflectionClass(RiskRankSubscriber::class);
        $method = $reflectionClass->getMethod('onCodeToSessionResponse');
        $attributes = $method->getAttributes(AsEventListener::class);

        $this->assertNotEmpty($attributes, 'AsEventListener attribute should be present on onCodeToSessionResponse method');
    }
}
