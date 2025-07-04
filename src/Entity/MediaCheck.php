<?php

namespace WechatMiniProgramSecurityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

#[ORM\Entity(repositoryClass: MediaCheckRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_media_check', options: ['comment' => '媒体文件检查'])]
class MediaCheck implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }
    use TimestampableAware;

    #[CreateIpColumn]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    private ?string $updatedFromIp = null;

#[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => '字段说明'])]
    private ?string $openId = null;

#[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $unionId = null;

#[ORM\Column(type: Types::STRING, length: 190, unique: true, options: ['comment' => '字段说明'])]
    private ?string $mediaUrl = null;

#[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '字段说明'])]
    private ?string $traceId = null;

#[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '字段说明'])]
    private ?bool $risky = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    private ?string $rawData = null;

    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(string $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): self
    {
        $this->openId = $openId;

        return $this;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): self
    {
        $this->unionId = $unionId;

        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): self
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    public function setTraceId(?string $traceId): self
    {
        $this->traceId = $traceId;

        return $this;
    }

    public function isRisky(): ?bool
    {
        return $this->risky;
    }

    public function setRisky(?bool $risky): self
    {
        $this->risky = $risky;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
