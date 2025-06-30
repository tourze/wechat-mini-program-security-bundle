<?php

namespace WechatMiniProgramSecurityBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use WechatMiniProgramSecurityBundle\WechatMiniProgramSecurityBundle;

class WechatMiniProgramSecurityBundleTest extends TestCase
{
    private WechatMiniProgramSecurityBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new WechatMiniProgramSecurityBundle();
    }

    public function test_bundle_extendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function test_bundle_implementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function test_getBundleDependencies_returnsExpectedDependencies(): void
    {
        $dependencies = WechatMiniProgramSecurityBundle::getBundleDependencies();

        $this->assertArrayHasKey(\Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class, $dependencies);
        $this->assertArrayHasKey(\Tourze\Symfony\CronJob\CronJobBundle::class, $dependencies);

        $this->assertEquals(['all' => true], $dependencies[\Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class]);
        $this->assertEquals(['all' => true], $dependencies[\Tourze\Symfony\CronJob\CronJobBundle::class]);
    }

    public function test_getBundleDependencies_returnsCorrectNumberOfDependencies(): void
    {
        $dependencies = WechatMiniProgramSecurityBundle::getBundleDependencies();

        $this->assertCount(2, $dependencies);
    }

    public function test_bundle_canBeInstantiated(): void
    {
        $bundle = new WechatMiniProgramSecurityBundle();
        $this->assertNotNull($bundle);
    }

    public function test_bundle_hasCorrectName(): void
    {
        $expectedName = 'WechatMiniProgramSecurityBundle';
        $this->assertEquals($expectedName, $this->bundle->getName());
    }
}