<?php

namespace WechatMiniProgramSecurityBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Request\GetUserRiskRankRequest;

class GetUserRiskRankRequestTest extends TestCase
{
    public function testRequestExists(): void
    {
        $this->assertTrue(class_exists(GetUserRiskRankRequest::class));
    }
}