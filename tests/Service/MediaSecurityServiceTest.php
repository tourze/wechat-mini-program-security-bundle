<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineAsyncBundle\Service\DoctrineService;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;
use WechatMiniProgramSecurityBundle\Request\MediaCheckAsyncRequest;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;
use Yiisoft\Json\Json;

class MediaSecurityServiceTest extends TestCase
{
    private MediaCheckRepository|MockObject $mediaCheckRepository;
    private DoctrineService|MockObject $doctrineService;
    private Client|MockObject $client;
    private MediaSecurityService $service;

    protected function setUp(): void
    {
        $this->mediaCheckRepository = $this->createMock(MediaCheckRepository::class);
        $this->doctrineService = $this->createMock(DoctrineService::class);
        $this->client = $this->createMock(Client::class);
        
        $this->service = new MediaSecurityService(
            $this->mediaCheckRepository,
            $this->doctrineService,
            $this->client
        );
    }

    public function test_checkImage_withExistingMediaUrl_shouldReturnEarly()
    {
        // 准备测试数据
        $url = 'https://example.com/image.jpg';
        
        // 创建模拟小程序
        $miniProgram = $this->createMock(MiniProgramInterface::class);
        $miniProgram->method('getAppId')->willReturn('test_app_id');
        
        // 创建模拟用户
        $wechatUser = new MockWechatUser('open-id-1', 'union-id-1');
        $wechatUser->setMiniProgram($miniProgram);
        
        // 模拟已存在同一URL的记录
        $mediaCheck = new MediaCheck();
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $url])
            ->willReturn($mediaCheck);
            
        // 验证Client没有被调用
        $this->client->expects($this->never())
            ->method('request');
        
        // 验证数据库操作没有被调用
        $this->doctrineService->expects($this->never())
            ->method('directInsert');
            
        // 执行测试方法
        $this->service->checkImage($wechatUser, $url);
    }
    
    public function test_checkImage_withNewMediaUrl_shouldCreateMediaCheck()
    {
        // 准备测试数据
        $url = 'https://example.com/new-image.jpg';
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        $traceId = 'test_trace_id';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟数据库没有同一URL的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $url])
            ->willReturn(null);
        
        // 验证请求参数    
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function (MediaCheckAsyncRequest $request) use ($openId, $url) {
                return $request->getOpenId() === $openId
                    && $request->getMediaUrl() === $url
                    && $request->getMediaType() === 2
                    && $request->getVersion() === 2
                    && $request->getScene() === 1;
            }))
            ->willReturn(['trace_id' => $traceId]);
        
        // 验证创建日志记录
        $this->doctrineService->expects($this->once())
            ->method('directInsert')
            ->with($this->callback(function (MediaCheck $mediaCheck) use ($openId, $unionId, $url, $traceId) {
                return $mediaCheck->getOpenId() === $openId
                    && $mediaCheck->getUnionId() === $unionId
                    && $mediaCheck->getMediaUrl() === $url
                    && $mediaCheck->getTraceId() === $traceId
                    && $mediaCheck->getRawData() === Json::encode(['trace_id' => $traceId]);
            }));
            
        // 执行测试方法
        $this->service->checkImage($wechatUser, $url);
    }
    
    public function test_checkImage_withInvalidResponse_shouldNotCreateMediaCheck()
    {
        // 准备测试数据
        $url = 'https://example.com/invalid-image.jpg';
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟数据库没有同一URL的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $url])
            ->willReturn(null);
            
        // 模拟客户端响应为无效响应（没有trace_id）
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn(['error' => 'some error']);
        
        // 验证不会创建日志记录
        $this->doctrineService->expects($this->never())
            ->method('directInsert');
            
        // 执行测试方法
        $this->service->checkImage($wechatUser, $url);
    }
    
    public function test_checkImage_withNullResponse_shouldNotCreateMediaCheck()
    {
        // 准备测试数据
        $url = 'https://example.com/null-response.jpg';
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        
        // 创建模拟小程序
        $miniProgram = $this->createMock(MiniProgramInterface::class);
        $miniProgram->method('getAppId')->willReturn('test_app_id');
        
        // 创建模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($miniProgram);
        
        // 模拟数据库没有同一URL的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $url])
            ->willReturn(null);
            
        // 模拟客户端响应为null
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn(null);
        
        // 验证不会创建日志记录
        $this->doctrineService->expects($this->never())
            ->method('directInsert');
            
        // 执行测试方法
        $this->service->checkImage($wechatUser, $url);
    }

    public function test_checkImage_withApiException_shouldNotCreateMediaCheck()
    {
        // 准备测试数据
        $url = 'https://example.com/exception.jpg';
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        
        // 创建模拟小程序
        $miniProgram = $this->createMock(MiniProgramInterface::class);
        $miniProgram->method('getAppId')->willReturn('test_app_id');
        
        // 创建模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($miniProgram);
        
        // 模拟数据库没有同一URL的记录
        $this->mediaCheckRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['mediaUrl' => $url])
            ->willReturn(null);
            
        // 模拟客户端抛出异常
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('API调用失败'));
        
        // 验证不会创建日志记录
        $this->doctrineService->expects($this->never())
            ->method('directInsert');
            
        // 执行测试方法，应当捕获异常而不中断
        $this->expectException(\Exception::class);
        $this->service->checkImage($wechatUser, $url);
    }
} 