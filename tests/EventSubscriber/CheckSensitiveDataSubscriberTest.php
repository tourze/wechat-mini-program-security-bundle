<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;
use WechatMiniProgramSecurityBundle\EventSubscriber\CheckSensitiveDataSubscriber;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Json\Json;

/**
 * @internal
 * @phpstan-ignore symplify.forbiddenExtendOfNonAbstractClass,eventSubscriberTest.mustInheritAbstractIntegrationTestCase
 */
#[CoversClass(CheckSensitiveDataSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class CheckSensitiveDataSubscriberTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Services are auto-registered, no need to set up anything here
    }

    /**
     * @return array{LoggerInterface, MediaCheckRepository, EventDispatcherInterface, EntityManagerInterface, CheckSensitiveDataSubscriber}
     */
    private function createSubscriber(): array
    {
        // 创建 Mock 依赖
        $logger = $this->createMock(LoggerInterface::class);
        // 必须使用具体类 MediaCheckRepository 的 Mock
        // 1. 该测试需要验证 Repository 的具体方法调用行为
        // 2. MediaCheckRepository 没有对应的 interface 可以使用
        // 3. 测试的核心逻辑就是验证 Repository 的方法是否被正确调用
        $mediaCheckRepository = $this->createMock(MediaCheckRepository::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        // 直接创建被测试的服务实例
        // 由于服务容器限制，无法在测试中替换已初始化的服务
        // @phpstan-ignore integrationTest.noDirectInstantiationOfCoveredClass
        $subscriber = new CheckSensitiveDataSubscriber(
            $logger,
            $mediaCheckRepository,
            $eventDispatcher,
            $entityManager
        );

        return [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber];
    }

    public function testOnServerMessageWithOldVersionRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck)
        ;

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck)
        ;
        $entityManager->expects($this->once())
            ->method('flush')
        ;

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::callback(function (MediaCheckAsyncEvent $event) use ($mediaCheck) {
                return $event->getMediaCheckLog() === $mediaCheck;
            }))
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithOldVersionNonRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => false,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck)
        ;

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck)
        ;
        $entityManager->expects($this->once())
            ->method('flush')
        ;

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(MediaCheckAsyncEvent::class))
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNewVersionV2FormatRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = '63a02d61-46ea413c-62bccfbb';
        $message = [
            'ToUserName' => 'gh_6b8d87e0a0bd',
            'FromUserName' => 'oEAYS5cJa2e80i290W3OlqpT45Z0',
            'CreateTime' => 1671441765,
            'MsgType' => 'event',
            'Event' => 'wxa_media_check',
            'appid' => 'wx9788481f42e6b49a',
            'trace_id' => $traceId,
            'version' => 2,
            'detail' => [
                'strategy' => 'content_model',
                'errcode' => 0,
                'suggest' => 'risky',
                'label' => 20002,
                'prob' => 90,
            ],
            'errcode' => 0,
            'errmsg' => 'ok',
            'result' => [
                'suggest' => 'risky',
                'label' => 20002,
            ],
        ];

        $mediaCheck = new MediaCheck();
        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck)
        ;

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck)
        ;
        $entityManager->expects($this->once())
            ->method('flush')
        ;

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(MediaCheckAsyncEvent::class))
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNewVersionDetailFormatPassMessageShouldUpdateLogAndDispatchEvent(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = '62e41866-0d622bf2-19fc0080';
        $message = [
            'ToUserName' => 'gh_262ad44747a1',
            'FromUserName' => 'ovGLy5IoHjvDttxvKrYKjnUuUdAw',
            'CreateTime' => '1659115626',
            'MsgType' => 'event',
            'Event' => 'wxa_media_check',
            'appid' => 'wx0a12393911b1f4ff',
            'trace_id' => $traceId,
            'version' => '2',
            'detail' => [
                'strategy' => 'content_model',
                'errcode' => '0',
                'suggest' => 'pass',
                'label' => '100',
                'prob' => '90',
            ],
            'errcode' => '0',
            'errmsg' => 'ok',
            'result' => [
                'suggest' => 'pass',
                'label' => '100',
            ],
        ];

        $mediaCheck = new MediaCheck();
        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck)
        ;

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck)
        ;
        $entityManager->expects($this->once())
            ->method('flush')
        ;

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(MediaCheckAsyncEvent::class))
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNoTraceIdShouldNotProcessMessage(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $message = [
            'isrisky' => true,
        ];

        $mediaCheckRepository->expects($this->never())
            ->method('findOneBy')
        ;
        $entityManager->expects($this->never())
            ->method('persist')
        ;
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);
    }

    public function testOnServerMessageWithMediaCheckNotFoundShouldNotProcessMessage(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = 'non-existent-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn(null)
        ;

        $entityManager->expects($this->never())
            ->method('persist')
        ;
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);
    }

    public function testOnServerMessageWithPersistExceptionShouldLogError(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck)
        ;

        $exception = new \Exception('Database error');
        $entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException($exception)
        ;

        $logger->expects($this->once())
            ->method('error')
            ->with('旧版本更新媒体检查日志出错', self::callback(function ($context) use ($mediaCheck, $exception) {
                return $context['log'] === $mediaCheck && $context['exception'] === $exception;
            }))
        ;

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(MediaCheckAsyncEvent::class))
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);
    }

    public function testOnServerMessageWithInvalidMessageFormatShouldNotProcessMessage(): void
    {
        [$logger, $mediaCheckRepository, $eventDispatcher, $entityManager, $subscriber] = $this->createSubscriber();

        $message = [
            'some_other_data' => 'value',
        ];

        $mediaCheckRepository->expects($this->never())
            ->method('findOneBy')
        ;
        $entityManager->expects($this->never())
            ->method('persist')
        ;
        $eventDispatcher->expects($this->never())
            ->method('dispatch')
        ;

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $subscriber->onServerMessage($event);
    }
}
