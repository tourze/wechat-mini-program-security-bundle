<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramBundle\Service\Client;

/**
 * WechatMiniProgramBundle\Service\Client 的简化测试模拟实现
 *
 * 这个类提供了微信客户端的简化测试模拟，使用标准的 PHPUnit Mock 机制。
 *
 * @internal 仅用于测试目的
 */
final class MockWechatClient
{
    private MockObject&Client $client;

    public function __construct()
    {
        $testCase = new /**
         * @internal
         */
        #[CoversClass(Client::class)] class('testMethod') extends TestCase {
            public function createClientMock(): MockObject&Client
            {
                $mock = $this->createMock(Client::class);

                $mock->method('getAccountAccessToken')
                    ->willReturn([
                        'access_token' => 'mock_access_token',
                        'expires_in' => 7200,
                        'start_time' => time(),
                    ])
                ;

                $mock->method('request')
                    ->willReturn(['errcode' => 0, 'errmsg' => 'success'])
                ;

                return $mock;
            }
        };

        $this->client = $testCase->createClientMock();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAccountAccessToken(MiniProgramInterface $account, bool $refresh = false): array
    {
        return $this->client->getAccountAccessToken($account, $refresh);
    }

    public function request(RequestInterface $request): mixed
    {
        return $this->client->request($request);
    }
}
