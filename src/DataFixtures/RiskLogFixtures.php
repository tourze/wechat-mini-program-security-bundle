<?php

namespace WechatMiniProgramSecurityBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

#[When(env: 'test')]
#[When(env: 'dev')]
class RiskLogFixtures extends Fixture
{
    public const HIGH_RISK_LOG_REFERENCE = 'high-risk-log';
    public const MEDIUM_RISK_LOG_REFERENCE = 'medium-risk-log';
    public const LOW_RISK_LOG_REFERENCE = 'low-risk-log';

    public function load(ObjectManager $manager): void
    {
        $highRiskLog = new RiskLog();
        $highRiskLog->setCreateTime(new \DateTimeImmutable('-1 day'));
        $highRiskLog->setRiskRank(4);
        $highRiskLog->setScene(1);
        $highRiskLog->setMobileNo('13800138000');
        $highRiskLog->setClientIp('192.168.1.100');
        $highRiskLog->setEmailAddress('highrisk@test.local');
        $highRiskLog->setExtendedInfo('高风险用户行为检测');
        $highRiskLog->setUnoinId('high_risk_unionid_123');
        $highRiskLog->setOpenId('high_risk_openid_456');
        $highRiskLog->setUnionId('high_risk_union_789');
        $manager->persist($highRiskLog);
        $this->addReference(self::HIGH_RISK_LOG_REFERENCE, $highRiskLog);

        $mediumRiskLog = new RiskLog();
        $mediumRiskLog->setCreateTime(new \DateTimeImmutable('-2 hours'));
        $mediumRiskLog->setRiskRank(2);
        $mediumRiskLog->setScene(2);
        $mediumRiskLog->setMobileNo('13900139000');
        $mediumRiskLog->setClientIp('10.0.0.50');
        $mediumRiskLog->setEmailAddress('mediumrisk@test.local');
        $mediumRiskLog->setExtendedInfo('中等风险行为监控');
        $mediumRiskLog->setUnoinId('medium_risk_unionid_234');
        $mediumRiskLog->setOpenId('medium_risk_openid_567');
        $mediumRiskLog->setUnionId('medium_risk_union_890');
        $manager->persist($mediumRiskLog);
        $this->addReference(self::MEDIUM_RISK_LOG_REFERENCE, $mediumRiskLog);

        $lowRiskLog = new RiskLog();
        $lowRiskLog->setCreateTime(new \DateTimeImmutable('-30 minutes'));
        $lowRiskLog->setRiskRank(0);
        $lowRiskLog->setScene(3);
        $lowRiskLog->setMobileNo('15000150000');
        $lowRiskLog->setClientIp('172.16.0.10');
        $lowRiskLog->setEmailAddress('lowrisk@test.local');
        $lowRiskLog->setExtendedInfo('正常用户行为');
        $lowRiskLog->setUnoinId('low_risk_unionid_345');
        $lowRiskLog->setOpenId('low_risk_openid_678');
        $lowRiskLog->setUnionId('low_risk_union_901');
        $manager->persist($lowRiskLog);
        $this->addReference(self::LOW_RISK_LOG_REFERENCE, $lowRiskLog);

        for ($i = 1; $i <= 10; ++$i) {
            $riskLog = new RiskLog();
            $riskLog->setCreateTime(new \DateTimeImmutable('-' . $i . ' hours'));
            $riskLog->setRiskRank(rand(0, 4));
            $riskLog->setScene(rand(1, 5));
            $riskLog->setMobileNo('138' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT));
            $riskLog->setClientIp('192.168.' . rand(1, 255) . '.' . rand(1, 255));
            $riskLog->setEmailAddress('test' . $i . '@test.local');
            $riskLog->setExtendedInfo('测试风险日志记录 ' . $i);
            $riskLog->setUnoinId('test_unionid_' . $i);
            $riskLog->setOpenId('test_openid_' . $i);
            $riskLog->setUnionId('test_union_' . $i);
            $manager->persist($riskLog);
        }

        $manager->flush();
    }
}
