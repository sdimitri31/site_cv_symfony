<?php

namespace App\Service;

use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

class SettingsManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createSetting(string $name, $value, $type): Setting
    {
        $setting = new Setting();
        $setting->setName($name);
        $setting->setValue($value);
        $setting->setType($type);

        if($this->settingExists($name))
            return $setting;

        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        return $setting;
    }

    private function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
            default:
                return $value;
        }
    }

    public function getSetting(string $name, $default = null)
    {
        $repository = $this->entityManager->getRepository(Setting::class);
        $setting = $repository->findOneBy(['name' => $name]);
        return $setting ? $this->castValue($setting->getValue(), $setting->getType()) : $default;
    }

    public function settingExists(string $name): bool
    {
        $setting = $this->getSetting($name);
        return $setting !== null;
    }
}
