<?php

namespace WechatMiniProgramSecurityBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\Symfony\CronJob\CronJobBundle;
use Tourze\Symfony\RuntimeContextBundle\RuntimeContextBundle;
use WechatMiniProgramAuthBundle\WechatMiniProgramAuthBundle;

class WechatMiniProgramSecurityBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineIndexedBundle::class => ['all' => true],
            CronJobBundle::class => ['all' => true],
            RuntimeContextBundle::class => ['all' => true],
            WechatMiniProgramAuthBundle::class => ['all' => true],
        ];
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
