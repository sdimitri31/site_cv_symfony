<?php

namespace App\EventListener;

use App\Controller\ImageUploadController;
use App\Entity\Biography;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private $projectDir;
    private ImageService $imageService;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, ParameterBagInterface $params, ImageService $imageService)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->imageService = $imageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onAfterEntityPersisted',
            AfterEntityUpdatedEvent::class => 'onAfterEntityUpdated',
        ];
    }

    public function onAfterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Biography) {
            $this->handleBiographyEvent($entity);
        }
    }

    public function onAfterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Biography) {
            $this->handleBiographyEvent($entity);
        }
    }

    private function handleBiographyEvent(Biography $biography): void
    {
        $content = $biography->getContent();
        $id = $biography->getId();
        $biographyTempDir = ImageUploadController::biographyTempDir;
        $biographyDir = ImageUploadController::biographyDir . $id . '/';

        $updatedContent = $this->imageService->handleContentImageUpload($content, $biographyTempDir, $biographyDir);

        if ($updatedContent !== $content) {
            $biography->setContent($updatedContent);
            $this->entityManager->flush();
        }
    }

}
