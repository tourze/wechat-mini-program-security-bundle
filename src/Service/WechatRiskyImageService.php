<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\RiskyImageDetectBundle\Service\RiskyImageDetector;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

// TODO: 临时禁用装饰器模式来解决测试环境循环依赖问题 (见 Issue #999)
// #[When(env: 'prod')]
// #[When(env: 'dev')]
// #[AsDecorator(decorates: RiskyImageDetector::class, onInvalid: ContainerInterface::NULL_ON_INVALID_REFERENCE)]
#[Autoconfigure(public: true)]
class WechatRiskyImageService implements RiskyImageDetector
{
    public function __construct(
        private readonly ?RiskyImageDetector $inner,
        private readonly MediaCheckRepository $mediaCheckRepository,
    ) {
    }

    public function isRiskyImage(string $image): bool
    {
        $mediaCheck = $this->mediaCheckRepository->findOneBy([
            'mediaUrl' => $image,
        ]);
        if (null === $mediaCheck) {
            return $this->inner?->isRiskyImage($image) ?? false;
        }

        // 有可能还没发布之前微信回调已经回来了
        return (bool) $mediaCheck->isRisky();
    }
}
