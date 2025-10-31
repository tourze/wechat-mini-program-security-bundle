<?php

namespace WechatMiniProgramSecurityBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

/**
 * @internal
 */
#[CoversClass(RiskLog::class)]
final class RiskLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new RiskLog();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'createTime' => ['createTime', new \DateTimeImmutable()];
        yield 'riskRank' => ['riskRank', 3];
        yield 'scene' => ['scene', 1001];
        yield 'mobileNo' => ['mobileNo', '13812345678'];
        yield 'clientIp' => ['clientIp', '192.168.1.1'];
        yield 'emailAddress' => ['emailAddress', 'test@example.com'];
        yield 'extendedInfo' => ['extendedInfo', 'Additional info'];
        yield 'unoinId' => ['unoinId', 'unique_request_id'];
        yield 'openId' => ['openId', 'test_open_id'];
        yield 'unionId' => ['unionId', 'test_union_id'];
    }

    public function testToString(): void
    {
        $riskLog = new RiskLog();

        $this->assertSame('0', (string) $riskLog);
    }
}
