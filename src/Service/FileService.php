<?php

namespace App\Service;

class FileService
{
    public function createFile($data, $model): string
    {
        $result ='';
        if ($model == 'drinkableWater') {
            //Création du répertoire
            $path = dirname(__DIR__, 1) . '/storage/DrinkingWaterQuality/';
            $result = $this->extracted($path, $data);
        }

        if ($model == 'airQuality') {
            //Création du répertoire
            $path = dirname(__DIR__, 1) . '/storage/AirQuality/';
            $result = $this->extracted($path, $data);
        }

        return $result;
    }

    /**
     * @param string $path
     * @param $data
     * @return string
     */
    public function extracted(string $path, $data): string
    {
        if (!file_exists($path)) mkdir($path, 0777, true);

        //Création du fichier
        $filename = 'data_' . date('Y') . '_' . date('m') . '_' . date('d') . '_' . date('H') . 'h'. date('i') . '.json';

        $result = file_put_contents($path . $filename, $data);
        if ($result) {
            $size = filesize($path . $filename);
            return 'You have generated the "' . $filename . '" file with a size of ' . $size . ' bytes';
        } else {
            return 'An error occurred while creating the file';
        }
    }
}