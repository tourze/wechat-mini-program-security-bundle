<?php

namespace WechatMiniProgramSecurityBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

/**
 * @method MediaCheck|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaCheck|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaCheck[]    findAll()
 * @method MediaCheck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaCheckRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaCheck::class);
    }
}
