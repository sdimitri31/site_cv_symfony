<?php

namespace App\Controller;

use App\Entity\Biography;
use App\Form\BiographyType;
use App\Repository\BiographyRepository;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/biography')]
class BiographyController extends AbstractController
{
    #[Route('/', name: 'app_biography_index', methods: ['GET'])]
    public function index(BiographyRepository $biographyRepository, SettingsManager $settingsManager): Response
    {
        $biographyId = $settingsManager->getSetting('biographypage_id_displayed');
        $biography = $biographyRepository->findOneBy(['id' => $biographyId], ['updatedAt' => 'DESC']);
        return $this->render('biography/index.html.twig', [
            'biography' => $biography,
        ]);
    }

    #[Route('/new', name: 'app_biography_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $biography = new Biography();
        $form = $this->createForm(BiographyType::class, $biography);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($biography);
            $entityManager->flush();

            return $this->redirectToRoute('app_biography_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('biography/new.html.twig', [
            'biography' => $biography,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_biography_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Biography $biography): Response
    {
        return $this->render('biography/show.html.twig', [
            'biography' => $biography,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_biography_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Biography $biography, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BiographyType::class, $biography);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_biography_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('biography/edit.html.twig', [
            'biography' => $biography,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_biography_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Biography $biography, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$biography->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($biography);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_biography_index', [], Response::HTTP_SEE_OTHER);
    }
}
