<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Tourze\RiskyImageDetectBundle\Service\RiskyImageDetector;
use WechatMiniProgramSecurityBundle\Repository\MediaCheckRepository;

#[AsDecorator(decorates: RiskyImageDetector::class)]
class WechatRiskyImageService implements RiskyImageDetector
{
    public function __construct(
        #[AutowireDecorated] private readonly RiskyImageDetector $inner,
        private readonly MediaCheckRepository $mediaCheckRepository,
    )
    {
    }

    public function isRiskyImage(string $image): bool
    {
        $mediaCheck = $this->mediaCheckRepository->findOneBy([
            'mediaUrl' => $image,
        ]);
        if (null === $mediaCheck) {
            return $this->inner->isRiskyImage($image);
        }

        // 有可能还没发布之前微信回调已经回来了
        return (bool) $mediaCheck->isRisky();
    }
}
