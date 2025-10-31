<?php

namespace WechatMiniProgramSecurityBundle\Request;

use HttpClientBundle\Request\AutoRetryRequest;
use WechatMiniProgramBundle\Request\WithAccountRequest;

class MsgSecurityCheckRequest extends WithAccountRequest implements AutoRetryRequest
{
    /**
     * @var int 接口版本号，2.0版本为固定值2
     */
    private int $version = 2;

    /**
     * @var string 用户的openid（用户需在近两小时访问过小程序）
     */
    private string $openId;

    /**
     * @var int 场景枚举值（1 资料；2 评论；3 论坛；4 社交日志）
     */
    private int $scene = 1;

    /**
     * @var string 需检测的文本内容，文本字数的上限为2500字，需使用UTF-8编码
     */
    private string $content;

    /**
     * @var string|null 用户昵称，需使用UTF-8编码
     */
    private ?string $nickname = null;

    /**
     * @var string|null 文本标题，需使用UTF-8编码
     */
    private ?string $title = null;

    public function getRequestPath(): string
    {
        return '/wxa/msg_sec_check';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        $arr = [
            'version' => $this->getVersion(),
            'openid' => $this->getOpenId(),
            'scene' => $this->getScene(),
            'content' => $this->getContent(),
        ];
        if (null !== $this->getNickname()) {
            $arr['nickname'] = $this->getNickname();
        }

        if (null !== $this->getTitle()) {
            $arr['title'] = $this->getTitle();
        }

        return [
            'json' => $arr,
        ];
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getMaxRetries(): int
    {
        return 3;
    }
}
