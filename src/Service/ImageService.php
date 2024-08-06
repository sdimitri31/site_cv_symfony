<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    public function handleImageUpload(string $content, string $tempDir, string $newDir): string
    {
        $absoluteTempDir = $this->projectDir . '/public' . $tempDir;
        $absoluteDir = $this->projectDir . '/public' . $newDir;

        // Create directory
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0777, true);
        }

        // Déplacer les fichiers d'images du répertoire temporaire vers le répertoire de destination
        $pattern = '/<img[^>]+src="([^"]+)"[^>]*>/i';
        preg_match_all($pattern, $content, $matches);
        foreach ($matches[1] as $src) {
            if (strpos($src, $tempDir) !== false) {
                $filename = basename($src);
                $this->moveFile($filename, $tempDir, $newDir);
            }
        }

        // Mettre à jour les chemins des images dans le contenu
        $updatedContent = self::updateImagePathsInHtml($content, $tempDir, $newDir);
        return $updatedContent;
    }

    private static function updateImagePathsInHtml(string $htmlContent, string $tempDir, string $newPath): string
    {
        return preg_replace_callback(
            '/(<img[^>]+src=")([^"]+)("[^>]*>)/i',
            function ($matches) use ($tempDir, $newPath) {
                $currentPath = $matches[2];

                // If currentPath is TMP dir we update to newPath
                if (strpos($currentPath, $tempDir) !== false) {
                    // Get file name
                    $fileName = basename($currentPath);
                    // Build new path
                    $newSrc = $newPath . $fileName;
                    // return new <img>
                    return $matches[1] . $newSrc . $matches[3];
                }

                // Return unchanged <img>
                return $matches[0];
            },
            $htmlContent
        );
    }

    public function moveFile(string $fileName, string $currentPath, string $targetPath): void{
        $absoluteCurrentPath = $this->projectDir . '/public' . $currentPath;
        $absoluteTargetPath = $this->projectDir . '/public' . $targetPath;

        // Create directory
        if (!is_dir($absoluteTargetPath)) {
            mkdir($absoluteTargetPath, 0777, true);
        }

        if (file_exists($absoluteCurrentPath . '/' . $fileName)) {
            rename($absoluteCurrentPath . '/' . $fileName, $absoluteTargetPath . '/' . $fileName);
        }
    }
}