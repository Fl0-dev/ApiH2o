<?php

namespace App\Controller;

use App\Repository\DrinkableWaterQualityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DrinkableWaterController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(DrinkableWaterQualityRepository $drinkableWaterQualityRepository, CacheInterface $cache): Response
    {
        //calcule le temps d'exécution
       // $stopwatch->start('execute');

        $value = $cache->get('my_cache_key', function (ItemInterface $item) use ($drinkableWaterQualityRepository) {

            $item->expiresAfter(3600);

            $mesures = $drinkableWaterQualityRepository->findBy(
                [],
                ['datePrelevement' => 'DESC']
            );
            return $mesures;
        });

        //$stopwatch->stop('execute');
        return $this->render('drinkable_water/index.html.twig', [
            'mesures' => $value,
        ]);
    }
}
