<?php

namespace App\Controller\Admin;

use App\Entity\Biography;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BiographyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Biography::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/biography/_form_theme.html.twig')
            ->overrideTemplate('crud/edit', 'admin/biography/edit.html.twig')
            ->overrideTemplate('crud/new', 'admin/biography/new.html.twig')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            TextEditorField::new('content')
                ->setLabel('Content'),
            DateTimeField::new('createdAt'),
            DateTimeField::new('updatedAt'),
        ];
    }

}
