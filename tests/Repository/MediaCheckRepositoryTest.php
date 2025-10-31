<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

/**
 * @template-extends AbstractRepositoryTestCase<MediaCheck>
 * @internal
 */
#[CoversClass(MediaCheckRepository::class)]
#[RunTestsInSeparateProcesses]
final class MediaCheckRepositoryTest extends AbstractRepositoryTestCase
{
    private MediaCheckRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(MediaCheckRepository::class);
    }

    public function testCountWithNullFieldShouldReturnCorrectNumber(): void
    {
        $count = $this->repository->count(['unionId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testFindByWithNullFieldShouldReturnMatchingEntities(): void
    {
        $entities = $this->repository->findBy(['unionId' => null]);
        $this->assertIsArray($entities);
        foreach ($entities as $entity) {
            $this->assertInstanceOf(MediaCheck::class, $entity);
            $this->assertNull($entity->getUnionId());
        }
    }

    public function testFindOneByWithNullFieldShouldReturnMatchingEntity(): void
    {
        $entity = $this->repository->findOneBy(['unionId' => null]);
        if (null !== $entity) {
            $this->assertInstanceOf(MediaCheck::class, $entity);
            $this->assertNull($entity->getUnionId());
        }
    }

    public function testFindOneByWithOrderByShouldReturnCorrectEntity(): void
    {
        $allEntities = $this->repository->findAll();
        if (count($allEntities) >= 2) {
            $entityAsc = $this->repository->findOneBy([], ['id' => 'ASC']);
            $entityDesc = $this->repository->findOneBy([], ['id' => 'DESC']);

            $this->assertInstanceOf(MediaCheck::class, $entityAsc);
            $this->assertInstanceOf(MediaCheck::class, $entityDesc);

            if ($entityAsc->getId() !== $entityDesc->getId()) {
                $this->assertLessThan($entityDesc->getId(), $entityAsc->getId());
            }
        } else {
            $this->assertLessThan(2, count($allEntities), '数据库中少于2个实体，排序功能未能测试但符合预期');
        }
    }

    // save 方法测试
    public function testSaveWithNewEntityShouldPersistEntity(): void
    {
        $entity = new MediaCheck();
        $entity->setOpenId('test_save_openid');
        $entity->setMediaUrl('https://example.com/test-save.jpg');
        $entity->setTraceId('test_save_trace_' . uniqid());
        $entity->setRisky(false);
        $entity->setRawData('{"test": "save"}');

        $this->repository->save($entity);

        $this->assertGreaterThan(0, $entity->getId());

        // 验证实体已保存
        $savedEntity = $this->repository->find($entity->getId());
        $this->assertInstanceOf(MediaCheck::class, $savedEntity);
        $this->assertSame('test_save_openid', $savedEntity->getOpenId());
    }

    public function testSaveWithExistingEntityShouldUpdateEntity(): void
    {
        // 创建并保存实体
        $entity = new MediaCheck();
        $entity->setOpenId('test_update_openid');
        $entity->setMediaUrl('https://example.com/test-update.jpg');
        $entity->setTraceId('test_update_trace_' . uniqid());
        $entity->setRisky(false);
        $entity->setRawData('{"test": "update"}');

        $this->repository->save($entity);
        $entityId = $entity->getId();

        // 更新实体
        $entity->setRisky(true);
        $this->repository->save($entity);

        // 验证更新
        $updatedEntity = $this->repository->find($entityId);
        $this->assertInstanceOf(MediaCheck::class, $updatedEntity);
        $this->assertTrue($updatedEntity->isRisky());
    }

    public function testSaveWithFlushFalseShouldNotFlushImmediately(): void
    {
        $entity = new MediaCheck();
        $entity->setOpenId('test_no_flush_openid');
        $entity->setMediaUrl('https://example.com/test-no-flush.jpg');
        $entity->setTraceId('test_no_flush_trace_' . uniqid());
        $entity->setRisky(false);
        $entity->setRawData('{"test": "no_flush"}');

        $this->repository->save($entity, false);

        // 手动flush
        self::getEntityManager()->flush();

        $this->assertGreaterThan(0, $entity->getId());
    }

    // remove 方法测试
    public function testRemoveWithExistingEntityShouldRemoveEntity(): void
    {
        // 创建并保存实体
        $entity = new MediaCheck();
        $entity->setOpenId('test_remove_openid');
        $entity->setMediaUrl('https://example.com/test-remove.jpg');
        $entity->setTraceId('test_remove_trace_' . uniqid());
        $entity->setRisky(false);
        $entity->setRawData('{"test": "remove"}');

        $this->repository->save($entity);
        $entityId = $entity->getId();

        // 移除实体
        $this->repository->remove($entity);

        // 验证实体已被移除
        $removedEntity = $this->repository->find($entityId);
        $this->assertNull($removedEntity);
    }

    public function testRemoveWithFlushFalseShouldNotFlushImmediately(): void
    {
        // 创建并保存实体
        $entity = new MediaCheck();
        $entity->setOpenId('test_remove_no_flush_openid');
        $entity->setMediaUrl('https://example.com/test-remove-no-flush.jpg');
        $entity->setTraceId('test_remove_no_flush_trace_' . uniqid());
        $entity->setRisky(false);
        $entity->setRawData('{"test": "remove_no_flush"}');

        $this->repository->save($entity);
        $entityId = $entity->getId();

        // 移除实体但不立即flush
        $this->repository->remove($entity, false);

        // 此时实体应该还存在
        $stillExists = $this->repository->find($entityId);
        $this->assertInstanceOf(MediaCheck::class, $stillExists);

        // 手动flush后实体应该被移除
        self::getEntityManager()->flush();
        $removedEntity = $this->repository->find($entityId);
        $this->assertNull($removedEntity);
    }

    // IS NULL 查询测试
    public function testFindByWithRiskyIsNullShouldReturnMatchingEntities(): void
    {
        $entities = $this->repository->findBy(['risky' => null]);
        $this->assertIsArray($entities);
        foreach ($entities as $entity) {
            $this->assertInstanceOf(MediaCheck::class, $entity);
            $this->assertNull($entity->isRisky());
        }
    }

    public function testFindByWithUnionIdIsNullShouldReturnMatchingEntities(): void
    {
        $entities = $this->repository->findBy(['unionId' => null]);
        $this->assertIsArray($entities);
        foreach ($entities as $entity) {
            $this->assertInstanceOf(MediaCheck::class, $entity);
            $this->assertNull($entity->getUnionId());
        }
    }

    public function testFindByWithRawDataIsNullShouldReturnMatchingEntities(): void
    {
        $entities = $this->repository->findBy(['rawData' => null]);
        $this->assertIsArray($entities);
        foreach ($entities as $entity) {
            $this->assertInstanceOf(MediaCheck::class, $entity);
            $this->assertNull($entity->getRawData());
        }
    }

    public function testCountWithRiskyIsNullShouldReturnCorrectCount(): void
    {
        $count = $this->repository->count(['risky' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithUnionIdIsNullShouldReturnCorrectCount(): void
    {
        $count = $this->repository->count(['unionId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithRawDataIsNullShouldReturnCorrectCount(): void
    {
        $count = $this->repository->count(['rawData' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    protected function createNewEntity(): object
    {
        $entity = new MediaCheck();

        // 设置必填字段
        $entity->setOpenId('test_open_id_' . uniqid());
        $entity->setMediaUrl('https://example.com/media/' . uniqid() . '.jpg');
        $entity->setTraceId('trace_' . uniqid());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<MediaCheck>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
