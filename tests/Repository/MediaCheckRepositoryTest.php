<?php

namespace WechatMiniProgramSecurityBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

class MediaCheckRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(MediaCheckRepository::class));
    }
}