<?php

namespace App\Command;

use App\Service\CallApiService;
use App\Service\DataBaseService;
use App\Service\FileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'apiH20:airQuality',
    description: 'Add a short description for your command',
)]
class ApiH20AirQualityCommand extends Command
{

    private CallApiService $callApiService;
    private FileService $fileService;
    private DataBaseService $dataBaseService;

    /**
     * @param CallApiService $callApiService
     * @param FileService $fileService
     */
    public function __construct(CallApiService $callApiService, FileService $fileService, DataBaseService $dataBaseService)
    {
        parent::__construct();
        $this->callApiService = $callApiService;
        $this->fileService = $fileService;
        $this->dataBaseService = $dataBaseService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('city', InputArgument::REQUIRED, 'The city name')
            ->addArgument('region', InputArgument::REQUIRED, 'The region name')
            ->addArgument('country', InputArgument::REQUIRED, 'The country name')
            ->addOption('saveMethod', null, InputOption::VALUE_OPTIONAL, 'save method', 'file')
            ->setHelp('This command is used to get the air quality of a city.')
        ;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $city = $input->getArgument('city');
        $region = $input->getArgument('region');
        $country = $input->getArgument('country');


        $params = [
            'city' => $city,
            'state' => $region,
            'country' => $country,
            'key' => '85ac8564-4755-4b91-8dcf-4b9e6638189c'

        ];

        $data = $this->callApiService->getData('GET','https://api.airvisual.com/v2/city', $params);

        if ($input->getOption('saveMethod') == 'file') {
            //encode en json
            $data = json_encode($data);

            $result = $this->fileService->createFile($data, 'airQuality');
            $io->success($result);
        } elseif ($input->getOption('saveMethod') == 'database') {
            //enregistrement en base de données
            $this->dataBaseService->saveData($data, 'airQuality');

            $io->success('Data saved in database');

        } else {
            $io->error('Invalid syntax for "saveMethod" => file or database');
            exit();
        }

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $city = $input->getArgument('city');
        $region = $input->getArgument('region');
        $country = $input->getArgument('country');
        $helper = $this->getHelper('question');

        while (!$city) {
            $question = new Question('Please enter the city name:');
            $city = $helper->ask($input, $output, $question);
            $this->city = $city;

        }
        while (!$region) {
            $question = new Question('Please enter the region name:');
            $region = $helper->ask($input, $output, $question);
            $this->region = $region;
        }
        while (!$country) {
            $question = new Question('Please enter the country name:');
            $country = $helper->ask($input, $output, $question);
            $this->country = $country;
        }

        $validate = new ConfirmationQuestion(
            sprintf('Are you sure you want to add %s, %s, %s?', $city, $region, $country),
            false,
            '/^(y|j)/i'
        );

        if ($helper->ask($input,$output,$validate))
        {
            $input->setArgument('city', $city);
            $input->setArgument('region', $region);
            $input->setArgument('country', $country);
        } else {
            $this->interact($input, $output);
        }




    }
}
