<?php

namespace WechatMiniProgramSecurityBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Event\MediaCheckAsyncEvent;

class MediaCheckAsyncEventTest extends TestCase
{
    public function testEventExists(): void
    {
        $this->assertTrue(class_exists(MediaCheckAsyncEvent::class));
    }
}