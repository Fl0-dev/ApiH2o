<?php

namespace App\Command;

use DateTime;

use GuzzleHttp\Client;
use Error;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;



#[AsCommand(
    name: 'apiH20:import',
    description: 'Import H20 data from API'
)]
class ApiH20ImportCommand extends Command
{
    public string $commandName = 'apiH20:import';
    public string $argumentCity = 'city';
    public string $optionParam = 'param';
    public string $optionResultsByPage = 'resultsByPage';
    public string $optionPageNumber = 'pageNumber';
    public string $optionMinDate = 'minDate';
    public string $optionMaxDate = 'maxDate';
    protected $minDate;
    protected $maxDate;


    protected function configure(): void
    {
        // à mettre à la suite de la commande dans le terminal

        $commandName = $this->commandName;
        $argumentCity = $this->argumentCity;
        $optionParam = $this->optionParam;
        $optionResultsByPage = $this->optionResultsByPage;
        $optionPageNumber = $this->optionPageNumber;
        $optionMinDate = $this->optionMinDate;
        $optionMaxDate = $this->optionMaxDate;

        $this
            ->addArgument($argumentCity, InputArgument::IS_ARRAY | InputOption::VALUE_OPTIONAL,'city name or city code to import')
            ->addOption($optionParam, null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption($optionResultsByPage, null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption($optionPageNumber, null, InputOption::VALUE_OPTIONAL, 'number of page', 1)
            ->addOption($optionMinDate, null, InputOption::VALUE_OPTIONAL, 'first date  aaaa/mm/jj')
            ->addOption($optionMaxDate, null, InputOption::VALUE_OPTIONAL, 'last date  aaaa/mm/jj')
            ->setHelp(<<<HELP
            This command <info>$commandName</info> is used to import data from the hb'eau API.
            
            PARAMETER :
                <info>$argumentCity</info> : city name or city code to import

            OPTIONS :
                <info>$optionParam</info> : one or more sampling parameter codes to import
                <info>$optionResultsByPage</info> : the desired number of results per page (default: 20)
                <info>$optionPageNumber</info> : the desired page number (default: 1)
                <info>$optionMinDate</info> : a start date of direct debit (default: one month before today)
                <info>$optionMaxDate</info> : a end date of direct debit (default: today) 
                
             EXAMPLE :
                <info>$commandName paris --param=6455,6489 --resultsByPage=10 --pageNumber=2 --minDate=2020/01/01 --maxDate=2020/04/01</info>
            HELP)
        ;

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


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $minDate = $input->getOption('minDate');
        $maxDate = $input->getOption('maxDate');
        $helper = $this->getHelper('question');

        $lastmonth = date('Y/m/d', strtotime('last month'));
        $now = date('Y/m/d', strtotime('now'));
  
        $minQuestion = new Question(sprintf('Provide the starting date [default: %s] : ' , $lastmonth),$lastmonth );
        $maxQuestion = new Question(sprintf('Provide the ending date [default: %s] : ',$now),$now );

        if(!$minDate)
        {
            $minDate = $helper->ask($input,$output,$minQuestion);
           
        }

        if(!$maxDate)
        {
            $maxDate = $helper->ask($input,$output,$maxQuestion);
        }

        if (!$this->validateDate($minDate))
            throw new Error('You gave the wrong format');
        
        if (!$this->validateDate($minDate))
            throw new Error('You gave the wrong format');
        if ($maxDate > $now)
            throw new Error("the date is too big");

        $validate = new ConfirmationQuestion(
            sprintf('You choose to get  %s to %s as date tape Y to confirm : ', $minDate , $maxDate),
            false,
            '/^(y|Y)/i'
        );
        if ($helper->ask($input,$output,$validate))
        {
            $this->minDate = $minDate;
            $this->maxDate = $maxDate;
        } else 
            throw new Error("validation faile");
    }

    protected function validateDate($date , $format = "Y/m/d" )
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
