<?php

namespace App\Service;

class MaintenanceService
{
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    public function isMaintenanceMode(): bool
    {
        return $this->settingsManager->getSetting('maintenance_mode', false);
    }
}
