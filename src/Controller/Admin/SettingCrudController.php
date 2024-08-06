<?php

namespace App\Controller\Admin;

use App\Entity\Setting;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Setting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Settings')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Setting')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Setting')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'admin/setting/setting_form.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        $typeField = ChoiceField::new('type')
            ->setChoices([
                'String' => 'string',
                'Integer' => 'integer',
                'Boolean' => 'boolean',
            ])
            ->setRequired(true)
            ->setHelp('Select the type of the value');

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name')->setRequired(true),
            $typeField,
            TextField::new('value'),
        ];
    }
}
