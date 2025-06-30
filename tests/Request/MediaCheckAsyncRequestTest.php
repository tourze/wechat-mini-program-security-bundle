<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;

class MediaCheckAsyncRequestTest extends TestCase
{
    public function testRequestExists(): void
    {
        $this->assertTrue(class_exists(MediaCheckAsyncRequest::class));
    }
}