<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\RiskyImageDetectBundle\Service\RiskyImageDetector;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramSecurityBundle\Service\WechatRiskyImageService;

class WechatRiskyImageServiceTest extends TestCase
{
    private RiskyImageDetector|MockObject $inner;
    private MediaCheckRepository|MockObject $mediaCheckRepository;
    private WechatRiskyImageService $service;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(RiskyImageDetector::class);
        $this->mediaCheckRepository = $this->createMock(MediaCheckRepository::class);
        
        $this->service = new WechatRiskyImageService(
            $this->inner,
            $this->mediaCheckRepository
        );
    }

    public function test_isRiskyImage_withoutMediaCheck_shouldDelegateToInner()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/image.jpg';
        
        // 模拟数据库查询
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn(null);
        
        // 模拟内部服务的行为
        $this->inner->expects($this->once())
            ->method('isRiskyImage')
            ->with($imageUrl)
            ->willReturn(true);
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_isRiskyImage_withRiskyMediaCheck_shouldReturnTrue()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/risky-image.jpg';
        
        // 创建MediaCheck并设置为有风险
        $mediaCheck = new MediaCheck();
        $mediaCheck->setRisky(true);
        
        // 模拟数据库查询找到有风险的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn($mediaCheck);
        
        // 内部服务不应被调用
        $this->inner->expects($this->never())
            ->method('isRiskyImage');
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_isRiskyImage_withSafeMediaCheck_shouldReturnFalse()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/safe-image.jpg';
        
        // 创建MediaCheck并设置为安全
        $mediaCheck = new MediaCheck();
        $mediaCheck->setRisky(false);
        
        // 模拟数据库查询找到安全的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn($mediaCheck);
        
        // 内部服务不应被调用
        $this->inner->expects($this->never())
            ->method('isRiskyImage');
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertFalse($result);
    }
    
    public function test_isRiskyImage_withNullRiskyValue_shouldReturnFalse()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/pending-image.jpg';
        
        // 创建MediaCheck，但是不设置risky值
        $mediaCheck = new MediaCheck();
        
        // 模拟数据库查询找到记录，但risky值为null
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn($mediaCheck);
        
        // 内部服务不应被调用
        $this->inner->expects($this->never())
            ->method('isRiskyImage');
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertFalse($result);
    }
    
    public function test_isRiskyImage_innerReturnsTrue_shouldReturnTrue()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/inner-risky-image.jpg';
        
        // 模拟数据库查询没有找到记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn(null);
        
        // 模拟内部服务返回有风险
        $this->inner->expects($this->once())
            ->method('isRiskyImage')
            ->with($imageUrl)
            ->willReturn(true);
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_isRiskyImage_innerReturnsFalse_shouldReturnFalse()
    {
        // 准备测试数据
        $imageUrl = 'https://example.com/inner-safe-image.jpg';
        
        // 模拟数据库查询没有找到记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn(null);
        
        // 模拟内部服务返回安全
        $this->inner->expects($this->once())
            ->method('isRiskyImage')
            ->with($imageUrl)
            ->willReturn(false);
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertFalse($result);
    }
    
    public function test_isRiskyImage_withEmptyUrl_shouldDelegateToInner()
    {
        // 准备测试数据
        $imageUrl = '';
        
        // 模拟数据库查询
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $imageUrl])
            ->willReturn(null);
        
        // 模拟内部服务的行为
        $this->inner->expects($this->once())
            ->method('isRiskyImage')
            ->with($imageUrl)
            ->willReturn(false);
            
        // 执行测试方法
        $result = $this->service->isRiskyImage($imageUrl);
        
        // 验证结果
        $this->assertFalse($result);
    }
} 