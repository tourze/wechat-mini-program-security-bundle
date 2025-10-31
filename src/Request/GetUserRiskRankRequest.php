<?php

namespace WechatMiniProgramSecurityBundle\Request;

use WechatMiniProgramBundle\Request\WithAccountRequest;

/**
 * 获取用户安全等级
 * 该接口用于根据提交的用户信息数据获取用户的安全等级 risk_rank（无需用户授权）。
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/safety-control-capability/getUserRiskRank.html
 * @see https://developers.weixin.qq.com/community/develop/doc/00064a570e41287861ed128d651800
 */
class GetUserRiskRankRequest extends WithAccountRequest
{
    /**
     * @var string 用户的openid
     */
    private string $openId;

    /**
     * @var int 场景值，0:注册，1:营销作弊
     */
    private int $scene;

    /**
     * @var string 用户访问源ip
     */
    private string $clientIp;

    /**
     * @var string|null 用户手机号
     */
    private ?string $mobileNumber = null;

    /**
     * @var string|null 用户邮箱地址
     */
    private ?string $emailAddress = null;

    /**
     * @var string|null 额外补充信息
     */
    private ?string $extendedInfo = null;

    /**
     * @var bool 默认值false。false：正式调用，true：测试调用
     */
    private bool $isTest = false;

    public function getRequestPath(): string
    {
        return '/wxa/getuserriskrank';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        $json = [
            'appid' => $this->getAccount()->getAppId(),
            'openid' => $this->getOpenId(),
            'scene' => $this->getScene(),
            'client_ip' => $this->getClientIp(),
            'is_test' => $this->isTest(),
        ];

        if (null !== $this->getMobileNumber()) {
            $json['mobile_no'] = $this->getMobileNumber();
        }
        if (null !== $this->getEmailAddress()) {
            $json['email_address'] = $this->getEmailAddress();
        }
        if (null !== $this->getExtendedInfo()) {
            $json['extended_info'] = $this->getExtendedInfo();
        }

        return [
            'json' => $json,
        ];
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): void
    {
        $this->openId = $openId;
    }

    public function getScene(): int
    {
        return $this->scene;
    }

    public function setScene(int $scene): void
    {
        $this->scene = $scene;
    }

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber(?string $mobileNumber): void
    {
        $this->mobileNumber = $mobileNumber;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getExtendedInfo(): ?string
    {
        return $this->extendedInfo;
    }

    public function setExtendedInfo(?string $extendedInfo): void
    {
        $this->extendedInfo = $extendedInfo;
    }

    public function isTest(): bool
    {
        return $this->isTest;
    }

    public function setIsTest(bool $isTest): void
    {
        $this->isTest = $isTest;
    }
}
