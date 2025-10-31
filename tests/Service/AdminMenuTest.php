<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use WechatMiniProgramSecurityBundle\Service\AdminMenu;

/**
 * AdminMenu服务测试
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup for AdminMenu tests
    }

    public function testInvokeAddsMenuItems(): void
    {
        $container = self::getContainer();
        $adminMenu = $container->get(AdminMenu::class);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        // @phpstan-ignore-next-line callable.nonCallable
        // @phpstan-ignore-next-line symplify.noDynamicName
        $adminMenu($rootItem);

        // 验证菜单结构
        $wechatMenu = $rootItem->getChild('微信小程序');
        self::assertNotNull($wechatMenu);

        $securityMenu = $wechatMenu->getChild('安全管理');
        self::assertNotNull($securityMenu);

        self::assertNotNull($securityMenu->getChild('内容检测记录'));
        self::assertNotNull($securityMenu->getChild('风险日志'));
    }
}
