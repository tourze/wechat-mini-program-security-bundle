<?php

namespace WechatMiniProgramSecurityBundle\Tests\Message;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Message\MediaCheckMessage;

/**
 * @internal
 */
#[CoversClass(MediaCheckMessage::class)]
final class MediaCheckMessageTest extends TestCase
{
    public function testMessageInstantiation(): void
    {
        $message = new MediaCheckMessage();

        $openId = 'test_open_id';
        $url = 'https://example.com/test.jpg';
        $fileKey = 'test_file_key';

        $message->setOpenId($openId);
        $message->setUrl($url);
        $message->setFileKey($fileKey);

        $this->assertInstanceOf(MediaCheckMessage::class, $message);
        $this->assertSame($openId, $message->getOpenId());
        $this->assertSame($url, $message->getUrl());
        $this->assertSame($fileKey, $message->getFileKey());
    }

    public function testMessageProperties(): void
    {
        $message = new MediaCheckMessage();

        $openId = 'another_open_id';
        $url = 'https://example.com/another.jpg';

        $message->setOpenId($openId);
        $message->setUrl($url);
        $message->setFileKey(null);

        $this->assertSame($openId, $message->getOpenId());
        $this->assertSame($url, $message->getUrl());
        $this->assertNull($message->getFileKey());
    }
}
