<?php

namespace WechatMiniProgramSecurityBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

/**
 * @extends AbstractCrudController<MediaCheck>
 * @noTest EasyAdmin CRUD 控制器通过功能测试进行测试
 */
#[AdminCrud(routePath: '/wechat-mini-program-security/media-check', routeName: 'wechat_mini_program_security_media_check')]
final class WechatMiniProgramSecurityMediaCheckCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaCheck::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            TextField::new('openId', 'Open ID'),
            TextField::new('unionId', 'Union ID'),
            UrlField::new('mediaUrl', 'Media URL'),
            TextField::new('traceId', 'Trace ID'),
            BooleanField::new('risky', 'Is Risky'),
            TextareaField::new('rawData', 'Raw Data')->hideOnIndex(),
            DateTimeField::new('createTime', 'Created At')->hideOnForm(),
            DateTimeField::new('updateTime', 'Updated At')->hideOnForm(),
            TextField::new('createdFromIp', 'Created From IP')->hideOnForm(),
            TextField::new('updatedFromIp', 'Updated From IP')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('openId'))
            ->add(TextFilter::new('unionId'))
            ->add(TextFilter::new('traceId'))
            ->add(BooleanFilter::new('risky'))
            ->add(DateTimeFilter::new('createTime'))
            ->add(TextFilter::new('createdFromIp'))
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Media Check')
            ->setEntityLabelInPlural('Media Checks')
            ->setSearchFields(['openId', 'unionId', 'mediaUrl', 'traceId', 'createdFromIp'])
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }
}
