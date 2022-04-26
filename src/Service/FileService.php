<?php

namespace App\Service;

class FileService
{
    public function createFile($data)
    {
        //Création du répertoire
        $path = dirname(__DIR__, 1) . '/Hubeau/DrinkingWaterQuality/';
        if (!file_exists($path)) mkdir($path, 0777, true);

        //Création du fichier
        $filename = 'data_' . date('Y') . '_' . date('m') . '_' . date('d') . '.json';

        $result = file_put_contents($path . $filename, $data);
        if ($result) {
            $size = filesize($path . $filename);
            return 'You have generated the "' . $filename . '" file with a size of ' . $size . ' bytes';
        } else {
            return 'An error occurred while creating the file';
        }
    }
}