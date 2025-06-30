<?php

namespace WechatMiniProgramSecurityBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Repository\RiskLogRepository;

class RiskLogRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(RiskLogRepository::class));
    }
}