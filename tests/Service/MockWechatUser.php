<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use Tourze\WechatMiniProgramUserContracts\UserInterface;
use WechatMiniProgramBundle\Entity\Account;

/**
 * 模拟微信用户类，实现真实代码中调用但接口未定义的方法
 */
class MockWechatUser implements UserInterface
{
    private string $openId;
    private ?string $unionId = null;
    private Collection $phoneNumbers;
    private MiniProgramInterface $miniProgram;
    private ?string $avatarUrl = null;
    private ?string $nickName = null;

    public function __construct(string $openId, ?string $unionId = null)
    {
        $this->openId = $openId;
        $this->unionId = $unionId;
        $this->phoneNumbers = new ArrayCollection();
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

    public function getMiniProgram(): MiniProgramInterface
    {
        return $this->miniProgram;
    }
    
    public function setMiniProgram(MiniProgramInterface $miniProgram): self
    {
        $this->miniProgram = $miniProgram;
        return $this;
    }
    
    /**
     * 添加真实代码中调用的方法
     */
    public function getAccount(): Account
    {
        if ($this->miniProgram instanceof Account) {
            return $this->miniProgram;
        }
        
        $account = new Account();
        $account->setAppId($this->miniProgram->getAppId());
        return $account;
    }
    
    /**
     * 添加真实代码中调用的方法
     */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }
    
    /**
     * 添加模拟的电话号码
     */
    public function addPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumbers->add(new class($phoneNumber) {
            private string $phoneNumber;
            
            public function __construct(string $phoneNumber)
            {
                $this->phoneNumber = $phoneNumber;
            }
            
            public function getPhoneNumber(): string
            {
                return $this->phoneNumber;
            }
        });
        
        return $this;
    }
    
    /**
     * 添加真实代码中调用的方法
     */
    public function getNickName(): ?string
    {
        return $this->nickName;
    }
    
    public function setNickName(?string $nickName): self
    {
        $this->nickName = $nickName;
        return $this;
    }
} 