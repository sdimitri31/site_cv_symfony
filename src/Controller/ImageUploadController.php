<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/upload')]
#[IsGranted('ROLE_ADMIN')]
class ImageUploadController extends AbstractController
{
    public const projectDir = '/uploads/images/project/';
    public const projectTempDir = '/uploads/images/project/temp/';
    public const biographyDir = '/uploads/images/biography/';
    public const biographyTempDir = '/uploads/images/biography/temp/';

    public function uploadImage(Request $request, string $path)
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public'. $path;
        $filename = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadDir, $filename);
        } catch (FileException $e) {
            return new JsonResponse(['success' => false, 'message' => 'Could not upload file'], 500);
        }

        return new JsonResponse(['success' => true, 'file' => $path . $filename]);
    }

    #[Route('/project/image', name: 'upload_project_image', methods: ['POST'])]
    public function uploadProjectImage(Request $request)
    {
        return $this->uploadImage($request, ImageUploadController::projectTempDir);
    }

    #[Route('/biography/image', name: 'upload_biography_image', methods: ['POST'])]
    public function uploadBiographyImage(Request $request)
    {
        return $this->uploadImage($request, ImageUploadController::biographyTempDir);
    }
}
