<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;

class MediaSecurityServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(MediaSecurityService::class));
    }
}