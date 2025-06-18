<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Service\SensitiveTextService;

class SensitiveTextServiceTest extends TestCase
{
    private SensitiveTextDetector|MockObject $inner;
    private Client|MockObject $client;
    private LoggerInterface|MockObject $logger;
    private CacheInterface|MockObject $cache;
    private UserRepository|MockObject $userRepository;
    private UserLoaderInterface|MockObject $userLoader;
    private CodeSessionLogRepository|MockObject $sessionLogRepository;
    private SensitiveTextService $service;
    private ItemInterface|MockObject $cacheItem;

    protected function setUp(): void
    {
        $this->inner = $this->createMock(SensitiveTextDetector::class);
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->sessionLogRepository = $this->createMock(CodeSessionLogRepository::class);
        $this->cacheItem = $this->createMock(ItemInterface::class);
        
        $this->service = new SensitiveTextService(
            $this->inner,
            $this->client,
            $this->logger,
            $this->cache,
            $this->userRepository,
            $this->userLoader,
            $this->sessionLogRepository
        );
    }

    public function test_isSensitiveText_withCacheHit_shouldReturnCachedResult()
    {
        // 准备测试数据
        $text = '测试文本';
        $user = $this->createMock(UserInterface::class);
        $cacheKey = 'WechatMiniProgramSecurityBundle_ContentSecurityService_' . md5($text);
        
        // 模拟缓存命中
        $this->cache->expects($this->once())
            ->method('get')
            ->with($cacheKey, $this->anything())
            ->willReturn(true);
            
        // 执行测试方法
        $result = $this->service->isSensitiveText($text, $user);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_checkSensitiveText_withNormalUser_shouldCallWechatAPI()
    {
        // 准备测试数据
        $text = '测试文本';
        $openId = 'test_open_id';
        $appId = 'test_app_id';
        $nickName = 'test_nickname';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId);
        $wechatUser->setMiniProgram($account);
        $wechatUser->setNickName($nickName);
        
        // 模拟普通用户
        $user = $this->createMock(UserInterface::class);
        
        // 模拟查找微信用户成功
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['bizUser' => $user])
            ->willReturn($wechatUser);
            
        // 模拟会话日志
        $sessionLog = new CodeSessionLog();
        $sessionLog->setOpenId($openId);
        $sessionLog->setAccount($account);
        $sessionLog->setCreateTime(new \DateTimeImmutable());
        
        // 模拟查找会话日志成功
        $this->sessionLogRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['openId' => $openId])
            ->willReturn($sessionLog);
            
        // 模拟微信API响应（安全内容）
        $apiResponse = [
            'result' => [
                'suggest' => 'pass',
                'label' => 100
            ]
        ];
        
        // 验证请求参数
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($apiResponse);
            
        // 内部检测器应该被调用
        $this->inner->expects($this->once())
            ->method('isSensitiveText')
            ->with($text)
            ->willReturn(false);
            
        // 执行测试方法
        $result = $this->service->checkSensitiveText($text, $user);
        
        // 验证结果
        $this->assertFalse($result);
    }
    
    public function test_checkSensitiveText_withRiskyContent_shouldReturnTrue()
    {
        // 准备测试数据
        $text = '敏感文本';
        $openId = 'test_open_id';
        $appId = 'test_app_id';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId);
        $wechatUser->setMiniProgram($account);
        $wechatUser->setNickName('测试用户');
        
        // 模拟普通用户
        $user = $this->createMock(UserInterface::class);
        
        // 模拟查找微信用户成功
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($wechatUser);
            
        // 模拟会话日志
        $sessionLog = new CodeSessionLog();
        $sessionLog->setOpenId($openId);
        $sessionLog->setAccount($account);
        $sessionLog->setCreateTime(new \DateTimeImmutable());
        
        // 模拟查找会话日志成功
        $this->sessionLogRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($sessionLog);
            
        // 模拟微信API响应（敏感内容）
        $apiResponse = [
            'result' => [
                'suggest' => 'risky',
                'label' => 20002 // 色情
            ]
        ];
        
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($apiResponse);
            
        // 内部检测器不应该被调用
        $this->inner->expects($this->never())
            ->method('isSensitiveText');
            
        // 执行测试方法
        $result = $this->service->checkSensitiveText($text, $user);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_checkSensitiveText_withoutWechatUser_shouldFallbackToInner()
    {
        // 准备测试数据
        $text = '测试文本';
        $user = $this->createMock(UserInterface::class);
        
        // 模拟找不到微信用户
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['bizUser' => $user])
            ->willReturn(null);
            
        // 创建QueryBuilder模拟对象
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->getMockBuilder(\Doctrine\ORM\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // 配置模拟对象的行为
        $mockQueryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('a.id', Criteria::DESC)
            ->willReturnSelf();
            
        $mockQueryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->with(1)
            ->willReturnSelf();
            
        $mockQueryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($mockQuery);
            
        $mockQuery->expects($this->once())
            ->method('getOneOrNullResult')
            ->willReturn(null);
            
        // 模拟 CodeSessionLogRepository 的 createQueryBuilder 方法
        $this->sessionLogRepository
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);
            
        // 内部检测器应该被调用
        $this->inner->expects($this->once())
            ->method('isSensitiveText')
            ->with($text)
            ->willReturn(true);
            
        // 客户端不应该被调用
        $this->client->expects($this->never())
            ->method('request');
            
        // 执行测试方法
        $result = $this->service->checkSensitiveText($text, $user);
        
        // 验证结果
        $this->assertTrue($result);
    }
    
    public function test_checkSensitiveText_withExpiredSession_shouldFallbackToInner()
    {
        // 准备测试数据
        $text = '测试文本';
        $openId = 'test_open_id';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId('test_app_id');
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟普通用户
        $user = $this->createMock(UserInterface::class);
        
        // 模拟查找微信用户成功
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($wechatUser);
            
        // 模拟会话日志（已过期超过2小时）
        $sessionLog = new CodeSessionLog();
        $sessionLog->setOpenId($openId);
        $sessionLog->setCreateTime((new \DateTimeImmutable())->modify('-3 hours'));
        
        // 模拟查找会话日志成功
        $this->sessionLogRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($sessionLog);
            
        // 验证记录警告日志
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('该微信上次访问微信的时间已经超出2小时，不能调用微信接口', $this->anything());
            
        // 客户端不应该被调用
        $this->client->expects($this->never())
            ->method('request');
            
        // 内部检测器应该被调用
        $this->inner->expects($this->once())
            ->method('isSensitiveText')
            ->with($text)
            ->willReturn(false);
            
        // 执行测试方法
        $result = $this->service->checkSensitiveText($text, $user);
        
        // 验证结果
        $this->assertFalse($result);
    }
    
    public function test_checkSensitiveText_withApiException_shouldFallbackToInner()
    {
        // 准备测试数据
        $text = '测试文本';
        $openId = 'test_open_id';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId('test_app_id');
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId);
        $wechatUser->setMiniProgram($account);
        $wechatUser->setNickName('测试用户');
        
        // 模拟普通用户
        $user = $this->createMock(UserInterface::class);
        
        // 模拟查找微信用户成功
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($wechatUser);
            
        // 模拟会话日志
        $sessionLog = new CodeSessionLog();
        $sessionLog->setOpenId($openId);
        $sessionLog->setAccount($account);
        $sessionLog->setCreateTime(new \DateTimeImmutable());
        
        // 模拟查找会话日志成功
        $this->sessionLogRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($sessionLog);
            
        // 模拟API异常
        $exception = new \Exception('API错误');
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
            
        // 验证记录错误日志
        $this->logger->expects($this->once())
            ->method('error')
            ->with('内容安全审核报错', $this->anything());
            
        // 内部检测器应该被调用
        $this->inner->expects($this->once())
            ->method('isSensitiveText')
            ->with($text)
            ->willReturn(false);
            
        // 执行测试方法
        $result = $this->service->checkSensitiveText($text, $user);
        
        // 验证结果
        $this->assertFalse($result);
    }
} 