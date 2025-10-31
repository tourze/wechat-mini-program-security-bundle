<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector;

// TODO: 临时禁用装饰器模式来解决测试环境循环依赖问题 (见 Issue #999)
// #[When(env: 'prod')]
// #[When(env: 'dev')]
// #[AsDecorator(decorates: SensitiveTextDetector::class, onInvalid: ContainerInterface::NULL_ON_INVALID_REFERENCE)]
#[Autoconfigure(public: true)]
class SensitiveTextService implements SensitiveTextDetector
{
    public function __construct(
        private readonly ?SensitiveTextDetector $inner,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/sec-check/security.msgSecCheck.html
     */
    public function isSensitiveText(string $text, ?UserInterface $user = null): bool
    {
        // 这里加一层缓存，减少后面的外部接口请求
        $cacheKey = 'WechatMiniProgramSecurityBundle_ContentSecurityService_' . md5($text);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text, $user) {
            $item->expiresAfter(60 * 60 * 24 * 7);
            $item->tag('WechatMiniProgramSecurityBundle');

            return $this->checkSensitiveText($text, $user);
        });
    }

    public function checkSensitiveText(string $text, ?UserInterface $user = null): bool
    {
        return $this->inner?->isSensitiveText($text) ?? false;
    }
}
