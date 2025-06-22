<?php

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;
use WechatMiniProgramSecurityBundle\Request\GetUserRiskRankRequest;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;

class UserRiskServiceTest extends TestCase
{
    private Client|MockObject $client;
    private LoggerInterface|MockObject $logger;
    private EntityManagerInterface|MockObject $entityManager;
    private UserRiskService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->service = new UserRiskService(
            $this->client,
            $this->logger,
            $this->entityManager
        );
    }

    public function test_checkWechatUser_withValidResponse_shouldCreateAndPersistRiskLog()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        $riskRank = 0;
        $phoneNumber = '13800138000';
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        $wechatUser->addPhoneNumber($phoneNumber);
        
        // 模拟客户端响应
        $response = [
            'risk_rank' => $riskRank,
            'unoin_id' => $unionId, // 注意这里故意保留原代码中的拼写错误
        ];
        
        // 验证请求参数
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function (GetUserRiskRankRequest $request) use ($openId, $clientIp, $scene) {
                return $request->getOpenId() === $openId
                    && $request->getClientIp() === $clientIp
                    && $request->getScene() === $scene;
                    // Note: setAccount and setMobileNumber are commented out in the service
            }))
            ->willReturn($response);
        
        // 验证EntityManager的调用
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (RiskLog $log) use ($openId, $unionId, $clientIp, $scene, $riskRank) {
                return $log->getOpenId() === $openId
                    && $log->getUnionId() === $unionId
                    && $log->getClientIp() === $clientIp
                    && $log->getScene() === $scene
                    && $log->getRiskRank() === $riskRank
                    && $log->getMobileNo() === null  // phoneNumbers logic is commented out
                    && $log->getUnoinId() === $unionId;
            }));
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        // 执行测试方法
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }
    
    public function test_checkWechatUser_withNoPhoneNumber_shouldNotSetMobileNo()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户（没有手机号）
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟客户端响应
        $response = [
            'risk_rank' => 0,
            'unoin_id' => $unionId,
        ];
        
        // 验证请求参数
        $this->client->expects($this->once())
            ->method('request')
            ->with($this->callback(function (GetUserRiskRankRequest $request) {
                return $request->getMobileNumber() === null;
            }))
            ->willReturn($response);
        
        // 验证EntityManager的调用
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (RiskLog $log) {
                return $log->getMobileNo() === null;
            }));
            
        // 执行测试方法
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }
    
    public function test_checkWechatUser_withApiPermissionError_shouldLogWarningAndRethrow()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟API权限错误
        $exception = new \Exception('小程序无该 api 权限', 48001);
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
        
        // 验证记录警告日志
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('小程序无该 api 权限', $this->anything());
            
        // 验证不会持久化
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->entityManager->expects($this->never())
            ->method('flush');
            
        // 执行测试方法，预期抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(48001);
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }
    
    public function test_checkWechatUser_withOpenIdTimeoutError_shouldLogWarningAndRethrow()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $appId = 'test_app_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        
        // 创建Account对象
        $account = new Account();
        $account->setAppId($appId);
        $account->setName('测试账号');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($account);
        
        // 模拟openid超时错误
        $exception = new \Exception('用户 openid 超时', 61010);
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
        
        // 验证记录警告日志
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('用户 openid 超时，需要用户用真机在小程序登录过才有效', $this->anything());
            
        // 验证不会持久化
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->entityManager->expects($this->never())
            ->method('flush');
            
        // 执行测试方法，预期抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(61010);
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }

    public function test_checkWechatUser_withOtherException_shouldRethrow()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        
        // 创建模拟小程序
        $miniProgram = $this->createMock(Account::class);
        $miniProgram->method('getAppId')->willReturn('test_app_id');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($miniProgram);
        
        // 模拟其他未知错误
        $exception = new \Exception('其他API错误', 50000);
        $this->client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);
        
        // 验证不应记录特定警告
        $this->logger->expects($this->never())
            ->method('warning');
            
        // 验证不会持久化
        $this->entityManager->expects($this->never())
            ->method('persist');
        $this->entityManager->expects($this->never())
            ->method('flush');
            
        // 执行测试方法，预期抛出异常
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(50000);
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }
    
    public function test_checkWechatUser_withHighRiskResponse_shouldPersistHighRiskLog()
    {
        // 准备测试数据
        $openId = 'test_open_id';
        $unionId = 'test_union_id';
        $clientIp = '192.168.1.1';
        $scene = 1;
        $riskRank = 4; // 高风险值
        
        // 创建模拟小程序
        $miniProgram = $this->createMock(Account::class);
        $miniProgram->method('getAppId')->willReturn('test_app_id');
        
        // 创建并配置模拟用户
        $wechatUser = new MockWechatUser($openId, $unionId);
        $wechatUser->setMiniProgram($miniProgram);
        
        // 模拟客户端返回高风险响应
        $response = [
            'risk_rank' => $riskRank,
            'unoin_id' => $unionId,
        ];
        
        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($response);
        
        // 验证EntityManager的调用，保存了高风险日志
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (RiskLog $log) use ($riskRank) {
                return $log->getRiskRank() === $riskRank;
            }));
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        // 执行测试方法
        $this->service->checkWechatUser($wechatUser, $scene, $clientIp);
    }
} 