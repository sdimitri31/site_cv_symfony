<?php
// tests/Controller/HomeControllerTest.php

namespace App\Tests\Controller;

use App\Service\SettingsManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\BiographyRepository;
use App\Repository\ProjectRepository;
use App\Entity\Biography;
use App\Entity\Project;

class HomeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        // Create a stub for the Biography entity
        $biography = $this->createMock(Biography::class);
        $biography->method('getId')->willReturn(1);
        $biography->method('getTitle')->willReturn('Biography Title');
        $biography->method('getContent')->willReturn('<p>This is the biography content.</p>');

        // Mocking the BiographyRepository
        $biographyRepository = $this->createMock(BiographyRepository::class);
        $biographyRepository->method('findOneBy')
            ->willReturn($biography);

        // Create stubs for the Project entity
        $project1 = $this->createMock(Project::class);
        $project1->method('getId')->willReturn(1);
        $project1->method('getTitle')->willReturn('Project 1');
        $project1->method('getShortDescription')->willReturn('Short description of project 1');
        $project1->method('getImage')->willReturn('image1.jpg');
        $project1->method('isVisible')->willReturn(true);

        $project2 = $this->createMock(Project::class);
        $project2->method('getId')->willReturn(2);
        $project2->method('getTitle')->willReturn('Project 2');
        $project2->method('getShortDescription')->willReturn('Short description of project 2');
        $project2->method('getImage')->willReturn('image2.jpg');
        $project2->method('isVisible')->willReturn(true);

        $project3 = $this->createMock(Project::class);
        $project3->method('getId')->willReturn(3);
        $project3->method('getTitle')->willReturn('Project 3');
        $project3->method('getShortDescription')->willReturn('Short description of project 3');
        $project3->method('getImage')->willReturn('image3.jpg');
        $project3->method('isVisible')->willReturn(true);

        // Mocking the ProjectRepository
        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->method('findBy')
            ->willReturn([$project1, $project2, $project3]);

        // Mocking the SettingsService
        $settingsManager = $this->createMock(SettingsManager::class);
        $settingsManager->method('get')
            ->will($this->returnValueMap([
                ['homepage_biography_id_displayed', -1, 1],
                ['homepage_number_of_project_displayed', 3, 3]
            ]));

        // Replacing the actual services with mocks
        $client->getContainer()->set('App\Repository\BiographyRepository', $biographyRepository);
        $client->getContainer()->set('App\Repository\ProjectRepository', $projectRepository);
        $client->getContainer()->set('App\Service\SettingsManager', $settingsManager);

        $crawler = $client->request('GET', '/home');

        // Assertions
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Biography Title');
        $this->assertCount(3, $crawler->filter('.card'));
        $this->assertSelectorTextContains('#project-1', 'Project 1');
        $this->assertSelectorTextContains('#project-2', 'Project 2');
        $this->assertSelectorTextContains('#project-3', 'Project 3');
    }
}
