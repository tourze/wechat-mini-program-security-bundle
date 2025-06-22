<?php

namespace WechatMiniProgramSecurityBundle\Service;

use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\Criteria;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tourze\SensitiveTextDetectBundle\Service\SensitiveTextDetector;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Service\Client;
use WechatMiniProgramSecurityBundle\Request\MsgSecurityCheckRequest;
use Yiisoft\Arrays\ArrayHelper;

#[AsDecorator(decorates: SensitiveTextDetector::class)]
class SensitiveTextService implements SensitiveTextDetector
{
    public function __construct(
        #[AutowireDecorated] private readonly SensitiveTextDetector $inner,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
        private readonly UserRepository $userRepository,
        private readonly UserLoaderInterface $userLoader,
        private readonly CodeSessionLogRepository $sessionLogRepository,
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
        $request = new MsgSecurityCheckRequest();
        $request->setScene(1);
        $request->setContent($text);

        $wechatUser = null;
        $sessionLog = null;
        if ((bool) $user) {
            $wechatUser = $this->userRepository->findOneBy(['bizUser' => $user]);
        }

        // 如果找不到的话，我们就选最后一个
        if (null === $wechatUser) {
            /** @var CodeSessionLog $sessionLog */
            $sessionLog = $this->sessionLogRepository
                ->createQueryBuilder('a')
                ->orderBy('a.id', Criteria::DESC)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            if (null === $sessionLog) {
                return $this->inner->isSensitiveText($text);
            }
            $wechatUser = $this->userLoader->loadUserByOpenId($sessionLog->getOpenId());
        }
        // 如果还是找不到，那我们就放弃
        if (null === $wechatUser) {
            return $this->inner->isSensitiveText($text);
        }

        // 查找进入小程序的记录
        if (null === $sessionLog) {
            $sessionLog = $this->sessionLogRepository->findOneBy(['openId' => $wechatUser->getOpenId()]);
        }
        if (null === $sessionLog) {
            return $this->inner->isSensitiveText($text);
        }

        $time1 = CarbonImmutable::parse($sessionLog->getCreateTime());
        $time2 = CarbonImmutable::now();
        $hours = $time1->diffInHours($time2);
        if ($hours >= 2) {
            $this->logger->warning('该微信上次访问微信的时间已经超出2小时，不能调用微信接口', [
                'time1' => $time1,
                'time2' => $time2,
            ]);

            return $this->inner->isSensitiveText($text);
        }

        $request->setOpenId($sessionLog->getOpenId());
        $request->setAccount($sessionLog->getAccount());
        // TODO: UserInterface does not have getNickName() method
        // $request->setNickname($wechatUser->getNickName());

        try {
            $result = $this->client->request($request);

            // $suggest = ArrayHelper::getValue($result['result'], 'suggest');
            $label = intval(ArrayHelper::getValue($result['result'], 'label'));
            // 命中标签枚举值，100 正常；10001 广告；20001 时政；20002 色情；20003 辱骂；20006 违法犯罪；20008 欺诈；20012 低俗；20013 版权；21000 其他
            if (100 !== $label) {
                return true;
            }
        } catch (\Throwable $exception) {
            $this->logger->error('内容安全审核报错', [
                'request' => $request,
                'exception' => $exception,
            ]);

            return $this->inner->isSensitiveText($text);
        }

        return $this->inner->isSensitiveText($text);
    }
}
