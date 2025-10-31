<?php

namespace WechatMiniProgramSecurityBundle\Request;

use WechatMiniProgramBundle\Request\WithAccountRequest;

/**
 * 音视频内容安全识别
 * 2023.04.02：目前还不支持视频内容的识别
 *
 * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/sec-center/sec-check/mediaCheckAsync.html
 * @see https://developers.weixin.qq.com/community/minihome/doc/0002cac64f06e8de4f8c3df305ec00
 */
class MediaCheckAsyncRequest extends WithAccountRequest
{
    private string $mediaUrl;

    /**
     * @var int 1:音频;2:图片
     */
    private int $mediaType;

    private int $version = 2;

    /**
     * @var string 用户的openid（用户需在近两小时访问过小程序）
     */
    private string $openId;

    /**
     * @var int 场景枚举值（1 资料；2 评论；3 论坛；4 社交日志）
     */
    private int $scene;

    public function getRequestPath(): string
    {
        return '/wxa/media_check_async';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        $json = [
            'media_url' => $this->getMediaUrl(),
            'media_type' => $this->getMediaType(),
            'version' => $this->getVersion(),
            'openid' => $this->getOpenId(),
            'scene' => $this->getScene(),
        ];

        return [
            'json' => $json,
        ];
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getMediaType(): int
    {
        return $this->mediaType;
    }

    public function setMediaType(int $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
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
}
