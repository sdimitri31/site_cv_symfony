<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextField::new('text'),
            TextField::new('link'),
            TextField::new('image'),
        ];


//        ImageField::new('image')
//            ->setBasePath('uploads/images/contact/')
//            ->setUploadDir('public/uploads/images/contact/')
//            ->setUploadedFileNamePattern('[slug]-[contenthash].[extension]'),
    }

}
