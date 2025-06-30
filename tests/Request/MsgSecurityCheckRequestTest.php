<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Request\MsgSecurityCheckRequest;

class MsgSecurityCheckRequestTest extends TestCase
{
    public function testRequestExists(): void
    {
        $this->assertTrue(class_exists(MsgSecurityCheckRequest::class));
    }
}