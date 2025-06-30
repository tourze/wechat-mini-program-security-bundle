<?php

namespace WechatMiniProgramSecurityBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Message\MediaCheckMessage;

class MediaCheckMessageTest extends TestCase
{
    public function testMessageExists(): void
    {
        $this->assertTrue(class_exists(MediaCheckMessage::class));
    }
}