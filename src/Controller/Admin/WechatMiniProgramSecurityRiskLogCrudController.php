<?php

namespace WechatMiniProgramSecurityBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramSecurityBundle\Entity\RiskLog;

/**
 * @extends AbstractCrudController<RiskLog>
 * @noTest EasyAdmin CRUD 控制器通过功能测试进行测试
 */
#[AdminCrud(routePath: '/wechat-mini-program-security/risk-log', routeName: 'wechat_mini_program_security_risk_log')]
final class WechatMiniProgramSecurityRiskLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RiskLog::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setLabel('ID'),
            DateTimeField::new('createTime', 'Create Time'),
            IntegerField::new('riskRank', 'Risk Rank'),
            IntegerField::new('scene', 'Scene'),
            TextField::new('mobileNo', 'Mobile No'),
            TextField::new('clientIp', 'Client IP'),
            TextField::new('emailAddress', 'Email Address'),
            TextField::new('extendedInfo', 'Extended Info'),
            TextField::new('unoinId', 'Unoin ID'),
            TextField::new('openId', 'Open ID'),
            TextField::new('unionId', 'Union ID'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('createTime'))
            ->add(NumericFilter::new('riskRank'))
            ->add(NumericFilter::new('scene'))
            ->add(TextFilter::new('mobileNo'))
            ->add(TextFilter::new('clientIp'))
            ->add(TextFilter::new('emailAddress'))
            ->add(TextFilter::new('openId'))
            ->add(TextFilter::new('unionId'))
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Risk Log')
            ->setEntityLabelInPlural('Risk Logs')
            ->setSearchFields(['openId', 'unionId', 'clientIp', 'mobileNo', 'emailAddress'])
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
        ;
    }
}
