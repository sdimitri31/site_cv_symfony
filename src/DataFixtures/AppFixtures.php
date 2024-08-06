<?php

namespace App\DataFixtures;

use App\Service\SettingsManager;
use App\Service\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture
{
    private $userManager;
    private $params;
    private $settings;

    public function __construct(UserManager $userManager, SettingsManager $settingsManager, ParameterBagInterface $params)
    {
        $this->userManager = $userManager;
        $this->params = $params;
        $this->settings = $settingsManager;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUsername = $this->params->get('env(ADMIN_USERNAME)');
        $adminPassword = $this->params->get('env(ADMIN_PASSWORD)');
        $adminEmail = $this->params->get('env(ADMIN_EMAIL)');

        if (!$this->userManager->adminExists()) {
            $this->userManager->createAdminUser($adminUsername, $adminPassword, $adminEmail);
        }

        $this->settings->createSetting('biographypage_id_displayed', '0', 'integer');
        $this->settings->createSetting('homepage_biography_id_displayed', '0', 'integer');
        $this->settings->createSetting('homepage_number_of_project_displayed', '3', 'integer');
        $this->settings->createSetting('allow_registration', 'false', 'boolean');
        $this->settings->createSetting('maintenance_mode', 'false', 'boolean');

    }
}
