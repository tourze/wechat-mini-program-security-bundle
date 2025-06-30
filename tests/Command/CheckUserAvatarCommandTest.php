<?php

namespace WechatMiniProgramSecurityBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramSecurityBundle\Command\CheckUserAvatarCommand;

class CheckUserAvatarCommandTest extends TestCase
{
    public function testCommandExists(): void
    {
        $this->assertTrue(class_exists(CheckUserAvatarCommand::class));
    }
}