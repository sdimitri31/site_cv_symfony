<?php

namespace App\Controller\Admin;

use App\Controller\ImageUploadController;
use App\Entity\Blog;
use App\Service\ImageService;
use Cocur\Slugify\Slugify;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BlogCrudController extends AbstractCrudController
{
    private ImageService $imageService;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $params, ImageService $imageService, LoggerInterface $logger)
    {
        $this->imageService = $imageService;
        $this->logger = $logger;

        $root = $params->get('kernel.project_dir');
        $dir = $root . '/public' . ImageUploadController::blogTempDir;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public static function getEntityFqcn(): string
    {
        return Blog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/blog/_form_theme.html.twig')
            ->overrideTemplate('crud/edit', 'admin/blog/edit.html.twig')
            ->overrideTemplate('crud/new', 'admin/blog/new.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $imageField = ImageField::new('newImage')
        ->setBasePath(ImageUploadController::blogTempDir)
        ->setUploadDir('public' . ImageUploadController::blogTempDir)
        ->setUploadedFileNamePattern(uniqid() . '.[extension]')
        ->setRequired($pageName !== Crud::PAGE_EDIT)
        ->setFormTypeOptions($pageName === Crud::PAGE_EDIT ? ['allow_delete' => false] : [])
        ->onlyOnForms();

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('slug')->onlyOnIndex(),
            TextField::new('title'),
            TextField::new('shortDescription'),
            TextEditorField::new('content'),
            DateTimeField::new('createdAt')->onlyOnIndex(),
            DateTimeField::new('updatedAt')->onlyOnIndex(),
            BooleanField::new('isVisible'),
            ArrayField::new('tags'),
            $imageField,
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $tempDir = ImageUploadController::blogTempDir;
        $newDir = ImageUploadController::blogDir . $entityInstance->getId() . '/';

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

        $tempDir = ImageUploadController::blogTempDir;
        $newDir = ImageUploadController::blogDir . $entityInstance->getId() . '/';

        $this->setContent($entityInstance, $tempDir, $newDir);
        $this->setImage($entityInstance, $tempDir, $newDir);

        $entityManager->flush();
    }

    private function generateSlug(Blog $blog): void
    {
        $slugify = new Slugify();
        $slug = $slugify->slugify($blog->getTitle());
        $blog->setSlug($slug);
    }

    private function setImage(Blog $blog, $tempDir, $newDir): void
    {
        $blog->setImage($blog->getNewImage());
        $this->imageService->moveFile($blog->getImage(), $tempDir, $newDir);
    }

    private function setContent(Blog $blog, $tempDir, $newDir): void
    {
        $updatedContent = $this->imageService->handleContentImageUpload($blog->getContent(), $tempDir, $newDir);
        if ($updatedContent !== $blog->getContent()) {
            $blog->setContent($updatedContent);
        }
    }
}
