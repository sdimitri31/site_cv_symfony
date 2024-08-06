<?php

// tests/Controller/BiographyControllerTest.php

namespace App\Tests\Controller;

use App\Service\SettingsManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\BiographyRepository;
use App\Entity\Biography;

class BiographyControllerTest extends WebTestCase
{
    public function testIndexWithBiography()
    {
        $client = static::createClient();

        // Create a mock for the Biography entity
        $biography = $this->createMock(Biography::class);
        $biography->method('getId')->willReturn(1);
        $biography->method('getTitle')->willReturn('Biography Title');
        $biography->method('getContent')->willReturn('<p>This is the biography content.</p>');

        // Mock BiographyRepository
        $biographyRepository = $this->createMock(BiographyRepository::class);
        $biographyRepository->method('findOneBy')
            ->willReturn($biography);

        // Mock SettingsService
        $settingsManager = $this->createMock(SettingsManager::class);
        $settingsManager->method('get')
            ->willReturn(1);

        // Replacing the actual services with mocks
        $client->getContainer()->set('App\Repository\BiographyRepository', $biographyRepository);
        $client->getContainer()->set('App\Service\SettingsManager', $settingsManager);

        $client->request('GET', '/biography/');

        // Assertions
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Biography Title');
        $this->assertSelectorTextContains('#biography-1', 'This is the biography content.');
    }

    public function testIndexWithoutBiography()
    {
        $client = static::createClient();

        // Mock BiographyRepository to return null
        $biographyRepository = $this->createMock(BiographyRepository::class);
        $biographyRepository->method('findOneBy')
            ->willReturn(null);

        // Mock SettingsService
        $settingsManager = $this->createMock(SettingsManager::class);
        $settingsManager->method('get')
            ->willReturn(-1);

        // Replacing the actual services with mocks
        $client->getContainer()->set('App\Repository\BiographyRepository', $biographyRepository);
        $client->getContainer()->set('App\Service\SettingsManager', $settingsManager);

        $client->request('GET', '/biography/');

        // Assertions
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Biographie');
        $this->assertSelectorTextContains('.row', 'Biographie');
    }

}
