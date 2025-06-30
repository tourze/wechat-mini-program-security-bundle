<?php

namespace WechatMiniProgramSecurityBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Controller\Admin\WechatMiniProgramSecurityRiskLogCrudController;

class WechatMiniProgramSecurityRiskLogCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(WechatMiniProgramSecurityRiskLogCrudController::class));
    }
}