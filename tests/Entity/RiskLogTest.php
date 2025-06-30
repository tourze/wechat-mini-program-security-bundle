<?php

namespace WechatMiniProgramSecurityBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

class RiskLogTest extends TestCase
{
    public function testEntityExists(): void
    {
        $this->assertTrue(class_exists(RiskLog::class));
    }
}