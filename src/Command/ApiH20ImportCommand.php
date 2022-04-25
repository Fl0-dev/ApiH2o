<?php

namespace App\Command;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'apiH20:import',
    description: 'Import H20 data from API'
)]
class ApiH20ImportCommand extends Command
{
    protected function configure(): void
    {
        // à mettre à la suite de la commande dans le terminal
        $this
            ->addArgument('city', InputArgument::IS_ARRAY, 'city name or city code to import')
            ->addOption('param', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption('resultsByPage', null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption('pageNumber', null, InputOption::VALUE_OPTIONAL, 'number of page', 1)
            ->addOption('minDate', null, InputOption::VALUE_OPTIONAL, 'first date', '01/01/2022')
            ->addOption('maxDate', null, InputOption::VALUE_OPTIONAL, 'last date', '01/04/2022')
            ->setHelp('Import H20 data from API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //Création du répertoire 
        $path = dirname(__DIR__, 1) . '/Hubeau/DrinkingWaterQuality/';
        if (!file_exists($path)) mkdir($path, 0777, true);

        //Argument city
        if (!empty($input->getArgument('city'))) {
            foreach ($input->getArgument('city') as $city) {
                if (preg_match("/^[a-zA-Z-]+$/", $city)) {
                    $params['nom_commune'][] = strtoupper($city);
                }
            }
        }

        //Option minDate
        try {
            $params['date_min_prelevement'] = (new DateTime($input->getOption('minDate')))->format('Y-m-d H:i:s');
        } catch (\Throwable $th) {
            $io->error('Invalid syntax for "minDate" => dd-mm-YYYY or YYYY-mm-dd');
            exit();
        }

        //Option maxDate
        try {
            $params['date_max_prelevement'] = (new DateTime($input->getOption('maxDate')))->format('Y-m-d H:i:s');
        } catch (\Throwable $th) {
            $io->error('Invalid syntax for "minDate" => dd-mm-YYYY or YYYY-mm-dd');
            exit();
        }

        dd($params);
        $client = new Client();
        $response = $client->request('GET', 'https://hubeau.eaufrance.fr/api/vbeta/qualite_eau_potable/resultats_dis', [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => $params,
            'http_errors' => false,
            'verify' => false
        ]);

        $data = $response->getBody();
        $filename = 'data_' . date('Y') . '_' . date('m') . '_' . date('d') . '.json';

        $result = file_put_contents($path . $filename, $data);

        if ($result) {
            $size = filesize($path . $filename);
            $io->success('You have generated the "' . $filename . '" file with a size of ' . $size . ' bytes');
        } else {
            $io->error('An error occurred while creating the file');
        }

        return Command::SUCCESS;
    }
}
