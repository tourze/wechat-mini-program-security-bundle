<?php

namespace WechatMiniProgramSecurityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

#[ORM\Entity(repositoryClass: MediaCheckRepository::class)]
#[ORM\Table(name: 'wechat_mini_program_media_check', options: ['comment' => '媒体文件检查'])]
class MediaCheck implements \Stringable
{
    use TimestampableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\Column(type: Types::STRING, length: 120, options: ['comment' => '字段说明'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private ?string $openId = null;

    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Length(max: 120)]
    private ?string $unionId = null;

    #[ORM\Column(type: Types::STRING, length: 190, unique: true, options: ['comment' => '字段说明'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 190)]
    #[Assert\Url]
    private ?string $mediaUrl = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '字段说明'])]
    #[Assert\Length(max: 100)]
    private ?string $traceId = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'bool')]
    private ?bool $risky = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '原始数据'])]
    #[Assert\Length(max: 65535)]
    private ?string $rawData = null;

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(string $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): void
    {
        $this->openId = $openId;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    public function setTraceId(?string $traceId): void
    {
        $this->traceId = $traceId;
    }

    public function isRisky(): ?bool
    {
        return $this->risky;
    }

    public function setRisky(?bool $risky): void
    {
        $this->risky = $risky;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
