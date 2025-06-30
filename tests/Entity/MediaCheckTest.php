<?php

namespace WechatMiniProgramSecurityBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

class MediaCheckTest extends TestCase
{
    public function testEntityExists(): void
    {
        $this->assertTrue(class_exists(MediaCheck::class));
    }
}