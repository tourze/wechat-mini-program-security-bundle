<?php

namespace WechatMiniProgramSecurityBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\DependencyInjection\WechatMiniProgramSecurityExtension;

class WechatMiniProgramSecurityExtensionTest extends TestCase
{
    public function testExtensionExists(): void
    {
        $this->assertTrue(class_exists(WechatMiniProgramSecurityExtension::class));
    }
}