<?php

namespace WechatMiniProgramSecurityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

/**
 * @method RiskLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RiskLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RiskLog[]    findAll()
 * @method RiskLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RiskLogRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiskLog::class);
    }
}
