<?php

namespace App\Service;

use App\Entity\AirQuality;
use App\Entity\DrinkableWaterQuality;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataBaseService
{
    private EntityManagerInterface $entityManager;
    //private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    /**
     * @throws \Exception
     */
    public function saveData($data, $model): void
    {

        if ($model == 'drinkableWater') {
            foreach ($data as $value) {
                $mesure = new DrinkableWaterQuality();
                $mesure->setNomCommune($value['nom_commune']);
                $mesure->setCodeCommune($value['code_commune']);
                $mesure->setLibelleParametre($value['libelle_parametre']);
                $mesure->setDatePrelevement(new \DateTime($value['date_prelevement']));
                $mesure->setResultatNumerique($value['resultat_numerique']);
                $mesure->setLibelleUnite($value['libelle_unite']);
                $mesure->setCodeDepartement($value['code_departement']);
                $mesure->setNomDepartement($value['nom_departement']);

                $this->entityManager->persist($mesure);
                $this->entityManager->flush();
            }

        }
        if ($model == 'airQuality') {
            $mesure = new AirQuality();
            $mesure->setCity($data['city']);
            $mesure->setState($data['state']);
            $mesure->setCountry($data['country']);
            $mesure->setDay(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $mesure->setTemperature($data['current']['weather']['tp']);
            $mesure->setHumidity($data['current']['weather']['hu']);
            $mesure->setIqaUs($data['current']['pollution']['aqius']);

            $this->entityManager->persist($mesure);
            $this->entityManager->flush();
        }
    }
}