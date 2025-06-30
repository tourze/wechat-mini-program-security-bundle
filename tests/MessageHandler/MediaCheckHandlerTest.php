<?php

namespace WechatMiniProgramSecurityBundle\Tests\MessageHandler;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\MessageHandler\MediaCheckHandler;

class MediaCheckHandlerTest extends TestCase
{
    public function testHandlerExists(): void
    {
        $this->assertTrue(class_exists(MediaCheckHandler::class));
    }
}