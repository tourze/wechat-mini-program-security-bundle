<?php

declare(strict_types=1);

namespace WechatMiniProgramSecurityBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

/**
 * 微信小程序安全管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('微信小程序')) {
            $item->addChild('微信小程序');
        }

        $wechatMenu = $item->getChild('微信小程序');
        if (null === $wechatMenu) {
            return;
        }

        // 添加安全管理子菜单
        if (null === $wechatMenu->getChild('安全管理')) {
            $wechatMenu->addChild('安全管理')
                ->setAttribute('icon', 'fas fa-shield-alt')
            ;
        }

        $securityMenu = $wechatMenu->getChild('安全管理');
        if (null === $securityMenu) {
            return;
        }

        $securityMenu->addChild('内容检测记录')
            ->setUri($this->linkGenerator->getCurdListPage(MediaCheck::class))
            ->setAttribute('icon', 'fas fa-search')
        ;

        $securityMenu->addChild('风险日志')
            ->setUri($this->linkGenerator->getCurdListPage(RiskLog::class))
            ->setAttribute('icon', 'fas fa-exclamation-triangle')
        ;
    }
}
