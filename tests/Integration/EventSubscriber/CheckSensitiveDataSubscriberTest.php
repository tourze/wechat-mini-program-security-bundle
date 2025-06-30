<?php

namespace WechatMiniProgramSecurityBundle\Tests\Integration\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;
use WechatMiniProgramSecurityBundle\EventSubscriber\CheckSensitiveDataSubscriber;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Json\Json;

class CheckSensitiveDataSubscriberTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private MediaCheckRepository|MockObject $mediaCheckRepository;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private EntityManagerInterface|MockObject $entityManager;
    private CheckSensitiveDataSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mediaCheckRepository = $this->createMock(MediaCheckRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->subscriber = new CheckSensitiveDataSubscriber(
            $this->logger,
            $this->mediaCheckRepository,
            $this->eventDispatcher,
            $this->entityManager
        );
    }

    public function test_onServerMessage_withOldVersionRiskyMessage_shouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (MediaCheckAsyncEvent $event) use ($mediaCheck) {
                return $event->getMediaCheckLog() === $mediaCheck;
            }));

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function test_onServerMessage_withOldVersionNonRiskyMessage_shouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => false,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MediaCheckAsyncEvent::class));

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function test_onServerMessage_withNewVersionV2FormatRiskyMessage_shouldUpdateLogAndDispatchEvent(): void
    {
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
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MediaCheckAsyncEvent::class));

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function test_onServerMessage_withNewVersionDetailFormatPassMessage_shouldUpdateLogAndDispatchEvent(): void
    {
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
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($mediaCheck);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MediaCheckAsyncEvent::class));

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function test_onServerMessage_withNoTraceId_shouldNotProcessMessage(): void
    {
        $message = [
            'isrisky' => true,
        ];

        $this->mediaCheckRepository->expects($this->never())
            ->method('findOneBy');
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);
    }

    public function test_onServerMessage_withMediaCheckNotFound_shouldNotProcessMessage(): void
    {
        $traceId = 'non-existent-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn(null);

        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);
    }

    public function test_onServerMessage_withPersistException_shouldLogError(): void
    {
        $traceId = 'test-trace-id';
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $mediaCheck = new MediaCheck();
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['traceId' => $traceId])
            ->willReturn($mediaCheck);

        $exception = new \Exception('Database error');
        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('旧版本更新媒体检查日志出错', $this->callback(function ($context) use ($mediaCheck, $exception) {
                return $context['log'] === $mediaCheck && $context['exception'] === $exception;
            }));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(MediaCheckAsyncEvent::class));

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);
    }

    public function test_onServerMessage_withInvalidMessageFormat_shouldNotProcessMessage(): void
    {
        $message = [
            'some_other_data' => 'value',
        ];

        $this->mediaCheckRepository->expects($this->never())
            ->method('findOneBy');
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);
    }
}