<?php

namespace WechatMiniProgramSecurityBundle\Message;

use Tourze\Symfony\Async\Message\AsyncMessageInterface;

class MediaCheckMessage implements AsyncMessageInterface
{
    /**
     * @var string 微信用户openId
     */
    private string $openId;

    private string $url;

    private ?string $fileKey = null;

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): void
    {
        $this->openId = $openId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getFileKey(): ?string
    {
        return $this->fileKey;
    }

    public function setFileKey(?string $fileKey): void
    {
        $this->fileKey = $fileKey;
    }
}
