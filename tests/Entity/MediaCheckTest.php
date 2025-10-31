<?php

namespace WechatMiniProgramSecurityBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

/**
 * @internal
 */
#[CoversClass(MediaCheck::class)]
final class MediaCheckTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new MediaCheck();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'openId' => ['openId', 'test_open_id'];
        yield 'unionId' => ['unionId', 'test_union_id'];
        yield 'mediaUrl' => ['mediaUrl', 'https://example.com/image.jpg'];
        yield 'traceId' => ['traceId', 'test_trace_id'];
        yield 'risky' => ['risky', true];
        yield 'rawData' => ['rawData', '{"test": "data"}'];
    }

    public function testToString(): void
    {
        $mediaCheck = new MediaCheck();

        $this->assertSame('0', (string) $mediaCheck);
    }
}
