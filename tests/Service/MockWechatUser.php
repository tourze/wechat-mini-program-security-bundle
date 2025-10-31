<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;

final class MockWechatUser implements UserInterface
{
    private string $openId;

    private ?string $unionId;

    private ?MiniProgramInterface $miniProgram = null;

    private ?string $avatarUrl = null;

    public function __construct(string $openId, ?string $unionId = null)
    {
        $this->openId = $openId;
        $this->unionId = $unionId;
    }

    public function getMiniProgram(): MiniProgramInterface
    {
        if (null === $this->miniProgram) {
            $this->miniProgram = new class implements MiniProgramInterface {
                public function getAppId(): string
                {
                    return 'test-app-id';
                }

                public function getAppSecret(): string
                {
                    return 'test-app-secret';
                }
            };
        }

        return $this->miniProgram;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function setMiniProgram(MiniProgramInterface $miniProgram): void
    {
        $this->miniProgram = $miniProgram;
    }
}
