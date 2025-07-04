<?php

namespace WechatMiniProgramSecurityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramSecurityBundle\Repository\RiskLogRepository;

#[AsScheduleClean(expression: '12 4 * * *', defaultKeepDay: 90, keepDayEnv: 'WECHAT_MP_RISK_LOG_PERSIST_DAY_NUM')]
#[ORM\Entity(repositoryClass: RiskLogRepository::class, readOnly: true)]
#[ORM\Table(name: 'wechat_mini_program_risk_log', options: ['comment' => '风险记录日志'])]
class RiskLog implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeImmutable $createTime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $user = null;

    /**
     * @var int|null 合法值为0,1,2,3,4，数字越大风险越高
     *
     * 恶意等级0：无明显恶意行为或恶意历史
     * 恶意等级1：轻微风险特征异常，如账号异常等
     * 恶意等级2：风险特征异常，如高危IP，严重批量操作等
     * 恶意等级3：具有较明显的恶意特征，如涉黑灰产等
     * 恶意等级4：具有明显的恶意特征，如黑产羊毛账号等
     */
    #[IndexColumn]
    private ?int $riskRank = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '场景值'])]
    private ?int $scene = null;

    #[ORM\Column(length: 40, nullable: true, options: ['comment' => '用户手机号'])]
    private ?string $mobileNo = null;

    #[ORM\Column(length: 20, options: ['comment' => '用户访问源ip'])]
    private ?string $clientIp = null;

    #[ORM\Column(length: 120, nullable: true, options: ['comment' => '用户邮箱地址'])]
    private ?string $emailAddress = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '额外补充信息'])]
    private ?string $extendedInfo = null;

    #[ORM\Column(length: 60, nullable: true, options: ['comment' => '唯一请求标识'])]
    private ?string $unoinId = null;

#[ORM\Column(length: 64, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $openId = null;

#[ORM\Column(length: 64, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $unionId = null;

    public function setCreateTime(?\DateTimeImmutable $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRiskRank(): ?int
    {
        return $this->riskRank;
    }

    public function setRiskRank(?int $riskRank): self
    {
        $this->riskRank = $riskRank;

        return $this;
    }

    public function getScene(): ?int
    {
        return $this->scene;
    }

    public function setScene(int $scene): self
    {
        $this->scene = $scene;

        return $this;
    }

    public function getMobileNo(): ?string
    {
        return $this->mobileNo;
    }

    public function setMobileNo(?string $mobileNo): self
    {
        $this->mobileNo = $mobileNo;

        return $this;
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function setClientIp(?string $clientIp): self
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getExtendedInfo(): ?string
    {
        return $this->extendedInfo;
    }

    public function setExtendedInfo(?string $extendedInfo): self
    {
        $this->extendedInfo = $extendedInfo;

        return $this;
    }

    public function getUnoinId(): ?string
    {
        return $this->unoinId;
    }

    public function setUnoinId(?string $unoinId): self
    {
        $this->unoinId = $unoinId;

        return $this;
    }

    public function getOpenId(): ?string
    {
        return $this->openId;
    }

    public function setOpenId(?string $openId): static
    {
        $this->openId = $openId;

        return $this;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): static
    {
        $this->unionId = $unionId;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
