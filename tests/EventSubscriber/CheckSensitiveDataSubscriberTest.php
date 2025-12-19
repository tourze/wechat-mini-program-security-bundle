<?php

namespace WechatMiniProgramSecurityBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractEventSubscriberTestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\EventSubscriber\CheckSensitiveDataSubscriber;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramServerMessageBundle\Event\ServerMessageRequestEvent;
use Yiisoft\Json\Json;

#[CoversClass(CheckSensitiveDataSubscriber::class)]
#[RunTestsInSeparateProcesses]
final class CheckSensitiveDataSubscriberTest extends AbstractEventSubscriberTestCase
{
    private ?MediaCheckRepository $mediaCheckRepository = null;
    private ?CheckSensitiveDataSubscriber $subscriber = null;

    protected function onSetUp(): void
    {
        $this->mediaCheckRepository = self::getService(MediaCheckRepository::class);
        $this->subscriber = self::getService(CheckSensitiveDataSubscriber::class);
    }

    private function createMediaCheck(string $traceId): MediaCheck
    {
        $mediaCheck = new MediaCheck();
        $mediaCheck->setTraceId($traceId);
        $mediaCheck->setOpenId('test-open-id');
        $mediaCheck->setMediaUrl('https://example.com/media.jpg');

        self::getEntityManager()->persist($mediaCheck);
        self::getEntityManager()->flush();

        return $mediaCheck;
    }

    public function testOnServerMessageWithOldVersionRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = 'test-trace-id-' . uniqid();
        $mediaCheck = $this->createMediaCheck($traceId);

        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        // Refresh entity from database
        self::getEntityManager()->refresh($mediaCheck);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithOldVersionNonRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = 'test-trace-id-' . uniqid();
        $mediaCheck = $this->createMediaCheck($traceId);

        $message = [
            'isrisky' => false,
            'trace_id' => $traceId,
        ];

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        // Refresh entity from database
        self::getEntityManager()->refresh($mediaCheck);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNewVersionV2FormatRiskyMessageShouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = '63a02d61-46ea413c-' . uniqid();
        $mediaCheck = $this->createMediaCheck($traceId);

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

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        // Refresh entity from database
        self::getEntityManager()->refresh($mediaCheck);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertTrue($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNewVersionDetailFormatPassMessageShouldUpdateLogAndDispatchEvent(): void
    {
        $traceId = '62e41866-0d622bf2-' . uniqid();
        $mediaCheck = $this->createMediaCheck($traceId);

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

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        $this->subscriber->onServerMessage($event);

        // Refresh entity from database
        self::getEntityManager()->refresh($mediaCheck);

        $this->assertEquals(Json::encode($message), $mediaCheck->getRawData());
        $this->assertFalse($mediaCheck->isRisky());
    }

    public function testOnServerMessageWithNoTraceIdShouldNotProcessMessage(): void
    {
        $message = [
            'isrisky' => true,
        ];

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        // This should not throw any exception and simply return early
        $this->subscriber->onServerMessage($event);

        // If we reach here, the test passes
        $this->assertTrue(true);
    }

    public function testOnServerMessageWithMediaCheckNotFoundShouldNotProcessMessage(): void
    {
        $traceId = 'non-existent-trace-id-' . uniqid();
        $message = [
            'isrisky' => true,
            'trace_id' => $traceId,
        ];

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        // This should not throw any exception and simply return early
        $this->subscriber->onServerMessage($event);

        // If we reach here, the test passes
        $this->assertTrue(true);
    }

    public function testOnServerMessageWithInvalidMessageFormatShouldNotProcessMessage(): void
    {
        $message = [
            'some_other_data' => 'value',
        ];

        $event = new ServerMessageRequestEvent();
        $event->setMessage($message);

        // This should not throw any exception and simply return early
        $this->subscriber->onServerMessage($event);

        // If we reach here, the test passes
        $this->assertTrue(true);
    }

    public function testSubscriberIsRegistered(): void
    {
        $this->assertInstanceOf(CheckSensitiveDataSubscriber::class, $this->subscriber);
    }
}
