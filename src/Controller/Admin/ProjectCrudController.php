<?php
namespace App\Controller\Admin;

use App\Controller\ImageUploadController;
use App\Entity\Project;
use App\Service\ImageService;
use Cocur\Slugify\Slugify;
use DateTimeZone;
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
    private ImageService $imageService;

    public function __construct(ParameterBagInterface $params, ImageService $imageService)
    {
        $this->imageService = $imageService;

        $projectDir = $params->get('kernel.project_dir');
        $dir = $projectDir . '/public' . ImageUploadController::projectTempDir;

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
        $imageField = ImageField::new('newImage')
            ->setBasePath(ImageUploadController::projectTempDir)
            ->setUploadDir('public' .  ImageUploadController::projectTempDir)
            ->setUploadedFileNamePattern(uniqid() . '.[extension]')
            ->setRequired($pageName !== Crud::PAGE_EDIT)
            ->setFormTypeOptions($pageName === Crud::PAGE_EDIT ? ['allow_delete' => false] : [])
            ->onlyOnForms();

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('slug')->onlyOnIndex(),
            TextField::new('title'),
            TextField::new('shortDescription'),
            TextEditorField::new('longDescription'),
            DateTimeField::new('createdAt')->onlyOnIndex(),
            DateTimeField::new('updatedAt')->onlyOnIndex(),
            IntegerField::new('position'),
            BooleanField::new('isVisible'),
            $imageField,
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $tempDir = ImageUploadController::projectTempDir;
        $newDir = ImageUploadController::projectDir . $entityInstance->getId() . '/';

        // Update 'image' value and move file to it's final destination
        if (!empty($entityInstance->getNewImage())) {
            $this->setImage($entityInstance, $tempDir, $newDir);
        }

        // Update img src in content and move files to their final destination
        $this->setContent($entityInstance, $tempDir, $newDir);

        try{
            $entityInstance->setUpdatedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Paris')));
        }
        catch(\Exception $e){
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }

        //Generate slug from title
        $this->generateSlug($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->generateSlug($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);

        $tempDir = ImageUploadController::projectTempDir;
        $newDir = ImageUploadController::projectDir . $entityInstance->getId() . '/';

        $this->setContent($entityInstance, $tempDir, $newDir);
        $this->setImage($entityInstance, $tempDir, $newDir);

        $entityManager->flush();
    }

    private function generateSlug(Project $project): void
    {
        $slugify = new Slugify();
        $slug = $slugify->slugify($project->getTitle());
        $project->setSlug($slug);
    }

    private function setImage(Project $project, $tempDir, $newDir): void
    {
        $project->setImage($project->getNewImage());
        $this->imageService->moveFile($project->getImage(), $tempDir, $newDir);
    }

    private function setContent(Project $project, $tempDir, $newDir): void
    {
        $updatedContent = $this->imageService->handleContentImageUpload($project->getLongDescription(), $tempDir, $newDir);
        if ($updatedContent !== $project->getLongDescription()) {
            $project->setLongDescription($updatedContent);
        }
    }
}
