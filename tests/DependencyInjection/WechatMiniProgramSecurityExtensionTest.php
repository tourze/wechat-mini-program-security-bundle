<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use WechatMiniProgramSecurityBundle\DependencyInjection\WechatMiniProgramSecurityExtension;
use WechatMiniProgramSecurityBundle\Service\MediaSecurityService;
use WechatMiniProgramSecurityBundle\Service\SensitiveTextService;
use WechatMiniProgramSecurityBundle\Service\UserRiskService;
use WechatMiniProgramSecurityBundle\Service\WechatRiskyImageService;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramSecurityExtension::class)]
final class WechatMiniProgramSecurityExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testLoadExtensionCreatesServiceDefinitions(): void
    {
        $extension = new WechatMiniProgramSecurityExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension->load([], $container);

        $this->assertTrue($container->hasDefinition(MediaSecurityService::class));
        $this->assertTrue($container->hasDefinition(SensitiveTextService::class));
        $this->assertTrue($container->hasDefinition(UserRiskService::class));
        $this->assertTrue($container->hasDefinition(WechatRiskyImageService::class));
    }

    public function testGetAlias(): void
    {
        $extension = new WechatMiniProgramSecurityExtension();

        $this->assertSame('wechat_mini_program_security', $extension->getAlias());
    }
}
