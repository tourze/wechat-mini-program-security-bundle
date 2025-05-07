<?php

namespace WechatMiniProgramSecurityBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use WechatMiniProgramSecurityBundle\Entity\MediaCheck;

class WechatMiniProgramSecurityMediaCheckCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MediaCheck::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
