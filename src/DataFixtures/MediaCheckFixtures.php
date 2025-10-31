<?php

namespace WechatMiniProgramSecurityBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

#[When(env: 'test')]
#[When(env: 'dev')]
class MediaCheckFixtures extends Fixture
{
    public const RISKY_MEDIA_REFERENCE = 'risky-media';
    public const SAFE_MEDIA_REFERENCE = 'safe-media';
    public const PENDING_MEDIA_REFERENCE = 'pending-media';

    public function load(ObjectManager $manager): void
    {
        $riskyMedia = new MediaCheck();
        $riskyMedia->setOpenId('risky_user_openid_123');
        $riskyMedia->setUnionId('risky_user_unionid_456');
        $riskyMedia->setMediaUrl('https://httpbin.org/image/jpeg');
        $riskyMedia->setTraceId('trace_risky_' . uniqid());
        $riskyMedia->setRisky(true);
        $riskyMedia->setRawData('{"result":{"suggest":"risky","label":"100","subLabel":"political"},"errCode":0}');
        $manager->persist($riskyMedia);
        $this->addReference(self::RISKY_MEDIA_REFERENCE, $riskyMedia);

        $safeMedia = new MediaCheck();
        $safeMedia->setOpenId('safe_user_openid_789');
        $safeMedia->setUnionId('safe_user_unionid_012');
        $safeMedia->setMediaUrl('https://httpbin.org/image/png');
        $safeMedia->setTraceId('trace_safe_' . uniqid());
        $safeMedia->setRisky(false);
        $safeMedia->setRawData('{"result":{"suggest":"pass","label":"100","subLabel":"normal"},"errCode":0}');
        $manager->persist($safeMedia);
        $this->addReference(self::SAFE_MEDIA_REFERENCE, $safeMedia);

        $pendingMedia = new MediaCheck();
        $pendingMedia->setOpenId('pending_user_openid_345');
        $pendingMedia->setMediaUrl('https://httpbin.org/image/svg');
        $pendingMedia->setTraceId('trace_pending_' . uniqid());
        $pendingMedia->setRisky(null);
        $pendingMedia->setRawData('{"result":{"suggest":"review","label":"200","subLabel":"suspicious"},"errCode":0}');
        $manager->persist($pendingMedia);
        $this->addReference(self::PENDING_MEDIA_REFERENCE, $pendingMedia);

        for ($i = 1; $i <= 5; ++$i) {
            $media = new MediaCheck();
            $media->setOpenId('test_openid_' . $i);
            $media->setMediaUrl('https://httpbin.org/image/jpeg?size=200&id=' . $i);
            $media->setTraceId('trace_test_' . $i . '_' . uniqid());
            $media->setRisky(0 === $i % 2);
            $media->setRawData('{"result":{"suggest":"' . (0 === $i % 2 ? 'risky' : 'pass') . '"},"errCode":0}');
            $manager->persist($media);
        }

        $manager->flush();
    }
}
