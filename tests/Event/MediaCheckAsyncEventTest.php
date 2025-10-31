<?php

namespace WechatMiniProgramSecurityBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;

/**
 * @internal
 */
#[CoversClass(MediaCheckAsyncEvent::class)]
final class MediaCheckAsyncEventTest extends AbstractEventTestCase
{
    public function testEventInstantiation(): void
    {
        $event = new MediaCheckAsyncEvent();
        $this->assertInstanceOf(MediaCheckAsyncEvent::class, $event);
    }

    public function testSetAndGetMediaCheckLog(): void
    {
        $mediaCheck = new MediaCheck();
        $mediaCheck->setOpenId('test-openid');
        $mediaCheck->setMediaUrl('https://example.com/test.jpg');

        $event = new MediaCheckAsyncEvent();
        $event->setMediaCheckLog($mediaCheck);

        $this->assertSame($mediaCheck, $event->getMediaCheckLog());
        $this->assertSame('test-openid', $event->getMediaCheckLog()->getOpenId());
    }
}
