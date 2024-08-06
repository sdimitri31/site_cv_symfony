<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/setting')]
#[IsGranted('ROLE_ADMIN')]
class SettingController extends AbstractController
{
    #[Route('/', name: 'app_setting_index', methods: ['GET'])]
    public function index(SettingRepository $settingRepository): Response
    {
        return $this->render('setting/index.html.twig', [
            'settings' => $settingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_setting_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($setting);
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('setting/new.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_setting_show', methods: ['GET'])]
    public function show(Setting $setting): Response
    {
        return $this->render('setting/show.html.twig', [
            'setting' => $setting,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('setting/edit.html.twig', [
            'setting' => $setting,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_setting_delete', methods: ['POST'])]
    public function delete(Request $request, Setting $setting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$setting->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($setting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_setting_index', [], Response::HTTP_SEE_OTHER);
    }
}
