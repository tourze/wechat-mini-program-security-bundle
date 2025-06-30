<?php

namespace WechatMiniProgramSecurityBundle\Tests\Controller\Admin;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Controller\Admin\WechatMiniProgramSecurityMediaCheckCrudController;

class WechatMiniProgramSecurityMediaCheckCrudControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(WechatMiniProgramSecurityMediaCheckCrudController::class));
    }
}