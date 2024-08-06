<?php

namespace App\Controller;

use App\Repository\BiographyRepository;
use App\Repository\ProjectRepository;
use App\Service\SettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(BiographyRepository $biographyRepository, ProjectRepository $projectRepository, SettingsManager $settingsManager): Response
    {
        $biography = null;
        $biographyId = $settingsManager->getSetting('homepage_biography_id_displayed', -1);
        if($biographyId > 0){
            $biography = $biographyRepository->findOneBy(['id' => $biographyId], ['updatedAt' => 'DESC']);
        }
        $latestProjects = $projectRepository->findBy(['isVisible' => '1'], ['updatedAt' => 'DESC'], $settingsManager->getSetting('homepage_number_of_project_displayed', 3));

        return $this->render('home/index.html.twig', [
            'biography' => $biography,
            'latest_projects' => $latestProjects
        ]);
    }
}
