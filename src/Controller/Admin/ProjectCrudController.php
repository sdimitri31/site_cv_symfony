<?php
namespace App\Controller\Admin;

use App\Entity\Project;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProjectCrudController extends AbstractCrudController
{

    public function __construct(ParameterBagInterface $params)
    {
        $projectDir = $params->get('kernel.project_dir');
        $dir = $projectDir . '/public/uploads/images/project/temp/';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/project/_form_theme.html.twig')
            ->overrideTemplate('crud/edit', 'admin/project/edit.html.twig')
            ->overrideTemplate('crud/new', 'admin/project/new.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $imageField = ImageField::new('image')
            ->setBasePath('uploads/images/project/temp/')
            ->setUploadDir('public/uploads/images/project/temp/')
            ->setUploadedFileNamePattern(uniqid() . '.[extension]')
            ->setRequired($pageName !== Crud::PAGE_EDIT)
            ->setFormTypeOptions($pageName === Crud::PAGE_EDIT ? ['allow_delete' => false] : [])
            ->onlyOnForms();

        if ($pageName === Crud::PAGE_EDIT) {
            $imageField->setFormTypeOption('mapped', false);
        }

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('slug')->onlyOnIndex(),
            TextField::new('title'),
            TextField::new('shortDescription'),
            TextEditorField::new('longDescription'),
            DateTimeField::new('createdAt'),
            DateTimeField::new('updatedAt'),
            IntegerField::new('position'),
            BooleanField::new('isVisible'),
            $imageField,
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $existingProject = $entityManager->getRepository(Project::class)->find($entityInstance->getId());

        if ($existingProject) {
            if (empty($entityInstance->getImage())) {
                $entityInstance->setImage($existingProject->getImage());
            }
        }

        $this->generateSlug($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->generateSlug($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function generateSlug(Project $project): void
    {
        $slugify = new Slugify();
        $slug = $slugify->slugify($project->getTitle());
        $project->setSlug($slug);
    }
}
