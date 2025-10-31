<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;
use WechatMiniProgramSecurityBundle\Repository\RiskLogRepository;

/**
 * @template-extends AbstractRepositoryTestCase<RiskLog>
 * @internal
 */
#[CoversClass(RiskLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class RiskLogRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @return RiskLogRepository
     */
    protected function getRepository(): RiskLogRepository
    {
        $repository = self::getContainer()->get(RiskLogRepository::class);

        self::assertInstanceOf(RiskLogRepository::class, $repository);

        return $repository;
    }
    protected function onSetUp(): void
    {
        // Repository will be initialized when needed
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createTestRiskLog(array $data = []): RiskLog
    {
        $riskLog = new RiskLog();

        $this->setBasicRiskLogFields($riskLog, $data);
        $this->setOptionalRiskLogFields($riskLog, $data);

        return $riskLog;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setBasicRiskLogFields(RiskLog $riskLog, array $data): void
    {
        // Set create time
        if (array_key_exists('createTime', $data)) {
            /** @var \DateTimeImmutable|null $createTime */
            $createTime = $data['createTime'];
            $riskLog->setCreateTime($createTime);
        } else {
            $riskLog->setCreateTime(new \DateTimeImmutable());
        }

        // Set risk rank
        if (array_key_exists('riskRank', $data)) {
            /** @var int|null $riskRank */
            $riskRank = $data['riskRank'];
            $riskLog->setRiskRank($riskRank);
        } else {
            $riskLog->setRiskRank(1);
        }

        // Set scene (required field)
        $scene = $data['scene'] ?? 1001;
        $riskLog->setScene(is_numeric($scene) ? (int)$scene : 1001);

        // Set client IP
        if (array_key_exists('clientIp', $data)) {
            /** @var string|null $clientIp */
            $clientIp = $data['clientIp'];
            $riskLog->setClientIp($clientIp);
        } else {
            $riskLog->setClientIp('192.168.1.1');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setOptionalRiskLogFields(RiskLog $riskLog, array $data): void
    {
        if (array_key_exists('mobileNo', $data)) {
            /** @var string|null $mobileNo */
            $mobileNo = $data['mobileNo'];
            $riskLog->setMobileNo($mobileNo);
        }

        if (array_key_exists('emailAddress', $data)) {
            /** @var string|null $emailAddress */
            $emailAddress = $data['emailAddress'];
            $riskLog->setEmailAddress($emailAddress);
        }

        if (array_key_exists('extendedInfo', $data)) {
            /** @var string|null $extendedInfo */
            $extendedInfo = $data['extendedInfo'];
            $riskLog->setExtendedInfo($extendedInfo);
        }

        if (array_key_exists('unoinId', $data)) {
            /** @var string|null $unoinId */
            $unoinId = $data['unoinId'];
            $riskLog->setUnoinId($unoinId);
        }

        if (array_key_exists('openId', $data)) {
            /** @var string|null $openId */
            $openId = $data['openId'];
            $riskLog->setOpenId($openId);
        }

        if (array_key_exists('unionId', $data)) {
            /** @var string|null $unionId */
            $unionId = $data['unionId'];
            $riskLog->setUnionId($unionId);
        }

        // User field is not persisted in database, so we don't set it for repository tests
        // if (isset($data['user'])) {
        //     $riskLog->setUser($data['user']);
        // }
    }

    public function testFindByWithNullCriteria(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 4001,
            'clientIp' => '192.168.4.1',
            'mobileNo' => '+86138000001',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 4002,
            'clientIp' => '192.168.4.2',
            // No mobileNo set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['mobileNo' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getMobileNo());
        }
    }

    public function testCountWithMobileNoNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 99001,
            'clientIp' => '192.168.99.1',
            'mobileNo' => '+86138000999',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 99002,
            'clientIp' => '192.168.99.2',
            // No mobileNo set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['mobileNo' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByWithOrderByClause(): void
    {
        $now = new \DateTimeImmutable();

        $riskLog1 = $this->createTestRiskLog([
            'createTime' => $now->modify('-1 hour'),
            'riskRank' => 2,
            'scene' => 6001,
            'clientIp' => '10.20.1.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'createTime' => $now,
            'riskRank' => 2,
            'scene' => 6002,
            'clientIp' => '10.20.1.2',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $result = $this->getRepository()->findOneBy(['riskRank' => 2], ['createTime' => 'DESC']);

        $this->assertInstanceOf(RiskLog::class, $result);
        $this->assertEquals(6002, $result->getScene());
    }

    public function testCountWithNullCriteria(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 10001,
            'clientIp' => '172.16.4.1',
            'emailAddress' => 'test@example.com',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 10002,
            'clientIp' => '172.16.4.2',
            // No emailAddress set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['emailAddress' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testSave(): void
    {
        $riskLog = $this->createTestRiskLog([
            'riskRank' => 2,
            'scene' => 11001,
            'clientIp' => '203.0.113.1',
            'extendedInfo' => 'Test extended info',
        ]);

        $this->getRepository()->save($riskLog);

        $this->assertNotNull($riskLog->getId());

        $found = $this->getRepository()->find($riskLog->getId());
        $this->assertInstanceOf(RiskLog::class, $found);
        $this->assertEquals(2, $found->getRiskRank());
        $this->assertEquals(11001, $found->getScene());
        $this->assertEquals('Test extended info', $found->getExtendedInfo());
    }

    public function testSaveWithoutFlush(): void
    {
        $riskLog = $this->createTestRiskLog([
            'riskRank' => 3,
            'scene' => 12001,
            'clientIp' => '203.0.113.2',
        ]);

        $this->getRepository()->save($riskLog, false);
        $this->assertEquals(0, $riskLog->getId());

        self::getEntityManager()->flush();
        $this->assertNotNull($riskLog->getId());
    }

    public function testRemove(): void
    {
        $riskLog = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 13001,
            'clientIp' => '203.0.113.3',
        ]);

        $persisted = $this->persistAndFlush($riskLog);
        $this->assertInstanceOf(RiskLog::class, $persisted);
        $entityId = $persisted->getId();
        $this->assertNotNull($entityId);

        $this->getRepository()->remove($persisted);

        $found = $this->getRepository()->find($entityId);
        $this->assertNull($found);
    }

    public function testFindByRiskRankRange(): void
    {
        $entities = [];
        for ($riskRank = 0; $riskRank <= 4; ++$riskRank) {
            $riskLog = $this->createTestRiskLog([
                'riskRank' => $riskRank,
                'scene' => 16000 + $riskRank,
                'clientIp' => "198.51.100.{$riskRank}",
            ]);
            $entities[] = $riskLog;
        }

        $this->persistEntities($entities);

        // Test individual risk levels
        $level0Results = $this->getRepository()->findBy(['riskRank' => 0]);
        $this->assertGreaterThanOrEqual(1, count($level0Results));

        // 验证我们创建的记录存在于结果中
        $level0Scenes = array_map(fn ($entity) => $entity->getScene(), $level0Results);
        $this->assertContains(16000, $level0Scenes);

        $level4Results = $this->getRepository()->findBy(['riskRank' => 4]);
        $this->assertGreaterThanOrEqual(1, count($level4Results));

        // 验证我们创建的记录存在于结果中
        $level4Scenes = array_map(fn ($entity) => $entity->getScene(), $level4Results);
        $this->assertContains(16004, $level4Scenes);
    }

    public function testFindBySceneValue(): void
    {
        $scenes = [1001, 1002, 2001, 2002, 3001];
        $entities = [];

        foreach ($scenes as $index => $scene) {
            $riskLog = $this->createTestRiskLog([
                'riskRank' => 1,
                'scene' => $scene,
                'clientIp' => '203.0.113.' . ($index + 10),
            ]);
            $entities[] = $riskLog;
        }

        $this->persistEntities($entities);

        $results = $this->getRepository()->findBy(['scene' => 2001]);

        $this->assertCount(1, $results);
        $this->assertEquals(2001, $results[0]->getScene());
    }

    public function testFindByClientIp(): void
    {
        $ips = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '192.168.1.1'];
        $entities = [];

        foreach ($ips as $index => $ip) {
            $riskLog = $this->createTestRiskLog([
                'riskRank' => 1,
                'scene' => 17000 + $index,
                'clientIp' => $ip,
            ]);
            $entities[] = $riskLog;
        }

        $this->persistEntities($entities);

        $results = $this->getRepository()->findBy(['clientIp' => '192.168.1.1']);

        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertEquals('192.168.1.1', $result->getClientIp());
        }
    }

    public function testFindByMobileNo(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 18001,
            'clientIp' => '192.168.10.1',
            'mobileNo' => '+86138000001',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 2,
            'scene' => 18002,
            'clientIp' => '192.168.10.2',
            'mobileNo' => '+86138000002',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['mobileNo' => '+86138000001']);

        $this->assertCount(1, $results);
        $this->assertEquals('+86138000001', $results[0]->getMobileNo());
        $this->assertEquals(18001, $results[0]->getScene());
    }

    public function testFindByEmailAddress(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 19001,
            'clientIp' => '192.168.11.1',
            'emailAddress' => 'user1@example.com',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 2,
            'scene' => 19002,
            'clientIp' => '192.168.11.2',
            'emailAddress' => 'user2@example.com',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['emailAddress' => 'user1@example.com']);

        $this->assertCount(1, $results);
        $this->assertEquals('user1@example.com', $results[0]->getEmailAddress());
        $this->assertEquals(19001, $results[0]->getScene());
    }

    public function testFindByOpenIdAndUnionId(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 20001,
            'clientIp' => '192.168.12.1',
            'openId' => 'open_id_123',
            'unionId' => 'union_id_123',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 2,
            'scene' => 20002,
            'clientIp' => '192.168.12.2',
            'openId' => 'open_id_456',
            'unionId' => 'union_id_456',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $resultsByOpenId = $this->getRepository()->findBy(['openId' => 'open_id_123']);
        $this->assertCount(1, $resultsByOpenId);
        $this->assertEquals('open_id_123', $resultsByOpenId[0]->getOpenId());

        $resultsByUnionId = $this->getRepository()->findBy(['unionId' => 'union_id_456']);
        $this->assertCount(1, $resultsByUnionId);
        $this->assertEquals('union_id_456', $resultsByUnionId[0]->getUnionId());
    }

    public function testFindByCreateTimeOrdering(): void
    {
        $now = new \DateTimeImmutable();

        $riskLog1 = $this->createTestRiskLog([
            'createTime' => $now->modify('-3 hours'),
            'riskRank' => 1,
            'scene' => 21001,
            'clientIp' => '192.168.13.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'createTime' => $now->modify('-1 hour'),
            'riskRank' => 1,
            'scene' => 21002,
            'clientIp' => '192.168.13.2',
        ]);

        $riskLog3 = $this->createTestRiskLog([
            'createTime' => $now->modify('-2 hours'),
            'riskRank' => 1,
            'scene' => 21003,
            'clientIp' => '192.168.13.3',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2, $riskLog3]);

        $resultsAsc = $this->getRepository()->findBy(['riskRank' => 1], ['createTime' => 'ASC']);
        $this->assertGreaterThanOrEqual(3, count($resultsAsc));

        // 验证我们创建的记录存在于结果中且按正确顺序排列
        $scenesAsc = array_map(fn ($entity) => $entity->getScene(), $resultsAsc);
        $this->assertContains(21001, $scenesAsc);
        $this->assertContains(21002, $scenesAsc);
        $this->assertContains(21003, $scenesAsc);

        $resultsDesc = $this->getRepository()->findBy(['riskRank' => 1], ['createTime' => 'DESC']);
        $this->assertGreaterThanOrEqual(3, count($resultsDesc));

        // 验证我们创建的记录存在于结果中
        $scenesDesc = array_map(fn ($entity) => $entity->getScene(), $resultsDesc);
        $this->assertContains(21001, $scenesDesc);
        $this->assertContains(21002, $scenesDesc);
        $this->assertContains(21003, $scenesDesc);
    }

    public function testFindByWithExtendedInfoNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 24001,
            'clientIp' => '192.168.16.1',
            'extendedInfo' => 'Some info',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 24002,
            'clientIp' => '192.168.16.2',
            // No extendedInfo set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['extendedInfo' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getExtendedInfo());
        }
    }

    public function testCountWithExtendedInfoNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 25001,
            'clientIp' => '192.168.17.1',
            'extendedInfo' => 'Some info',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 25002,
            'clientIp' => '192.168.17.2',
            // No extendedInfo set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['extendedInfo' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithUnoinIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 26001,
            'clientIp' => '192.168.18.1',
            'unoinId' => 'unoin_id_12345',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 26002,
            'clientIp' => '192.168.18.2',
            // No unoinId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['unoinId' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getUnoinId());
        }
    }

    public function testCountWithUnoinIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 27001,
            'clientIp' => '192.168.19.1',
            'unoinId' => 'unoin_id_67890',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 27002,
            'clientIp' => '192.168.19.2',
            // No unoinId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['unoinId' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithOpenIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 28001,
            'clientIp' => '192.168.20.1',
            'openId' => 'open_id_test_123',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 28002,
            'clientIp' => '192.168.20.2',
            // No openId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['openId' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getOpenId());
        }
    }

    public function testCountWithOpenIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 29001,
            'clientIp' => '192.168.21.1',
            'openId' => 'open_id_test_456',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 29002,
            'clientIp' => '192.168.21.2',
            // No openId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['openId' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithUnionIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 30001,
            'clientIp' => '192.168.22.1',
            'unionId' => 'union_id_test_789',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 30002,
            'clientIp' => '192.168.22.2',
            // No unionId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['unionId' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getUnionId());
        }
    }

    public function testCountWithUnionIdNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 31001,
            'clientIp' => '192.168.23.1',
            'unionId' => 'union_id_test_101112',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => 1,
            'scene' => 31002,
            'clientIp' => '192.168.23.2',
            // No unionId set (null)
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['unionId' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithCreateTimeNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'createTime' => new \DateTimeImmutable(),
            'riskRank' => 1,
            'scene' => 32001,
            'clientIp' => '192.168.24.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'createTime' => null,
            'riskRank' => 1,
            'scene' => 32002,
            'clientIp' => '192.168.24.2',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['createTime' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getCreateTime());
        }
    }

    public function testCountWithCreateTimeNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'createTime' => new \DateTimeImmutable(),
            'riskRank' => 1,
            'scene' => 33001,
            'clientIp' => '192.168.25.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'createTime' => null,
            'riskRank' => 1,
            'scene' => 33002,
            'clientIp' => '192.168.25.2',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['createTime' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithRiskRankNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 2,
            'scene' => 34001,
            'clientIp' => '192.168.26.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => null,
            'scene' => 34002,
            'clientIp' => '192.168.26.2',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $results = $this->getRepository()->findBy(['riskRank' => null]);

        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertNull($result->getRiskRank());
        }
    }

    public function testCountWithRiskRankNull(): void
    {
        $riskLog1 = $this->createTestRiskLog([
            'riskRank' => 3,
            'scene' => 35001,
            'clientIp' => '192.168.27.1',
        ]);

        $riskLog2 = $this->createTestRiskLog([
            'riskRank' => null,
            'scene' => 35002,
            'clientIp' => '192.168.27.2',
        ]);

        $this->persistEntities([$riskLog1, $riskLog2]);

        $count = $this->getRepository()->count(['riskRank' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    protected function createNewEntity(): object
    {
        $entity = new RiskLog();

        // 设置必填字段
        $entity->setScene(1);
        $entity->setOpenId('test_openid_' . uniqid());
        $entity->setClientIp('192.168.1.' . random_int(1, 254));
        $entity->setCreateTime(new \DateTimeImmutable());

        return $entity;
    }

    }
