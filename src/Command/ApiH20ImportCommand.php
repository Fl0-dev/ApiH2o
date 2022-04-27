<?php

namespace App\Command;

use App\Entity\DrinkableWaterQuality;
use App\Service\CallApiService;
use App\Service\FileService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


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
    public string $saveMethod = 'saveMethod';
    protected $minDate;
    protected $maxDate;

    private CallApiService $callApiService;
    private FileService $fileService;
    private EntityManagerInterface $entityManager;
    /**
     * @param CallApiService $callApiService
     * @param FileService $fileService
     */
    public function __construct(CallApiService $callApiService, FileService $fileService, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->callApiService = $callApiService;
        $this->fileService = $fileService;
        $this->entityManager = $entityManager;
    }

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
        $saveMethod = $this->saveMethod;

        $this
            ->addArgument($argumentCity, InputArgument::IS_ARRAY | InputOption::VALUE_OPTIONAL,'city name or city code to import')
            ->addOption($optionParam, null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption($optionResultsByPage, null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption($optionPageNumber, null, InputOption::VALUE_OPTIONAL, 'number of page', 1)

            ->addOption($optionMinDate, null, InputOption::VALUE_OPTIONAL, 'first date  aaaa/mm/jj')
            ->addOption($optionMaxDate, null, InputOption::VALUE_OPTIONAL, 'last date  aaaa/mm/jj')
            ->addOption($saveMethod, null, InputOption::VALUE_OPTIONAL, 'save method', 'file')
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
                <info>$saveMethod</info> : save method (default: file)
                
             EXAMPLE :
                <info>$commandName paris --param=6455,6489 --resultsByPage=10 --pageNumber=2 --minDate=2020/01/01 --maxDate=2020/04/01 --saveMethod=file</info>
            HELP)
        ;

    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fields = 'code_departement,nom_departement,code_commune,nom_commune,libelle_parametre,date_prelevement,resultat_numerique,libelle_unite';

        //Argument city
        if (!empty($input->getArgument('city'))) {
            foreach ($input->getArgument('city') as $city) {
                if (preg_match("/^[a-zA-Z-]+$/", $city)) {
                    $params['nom_commune'][] = strtoupper($city);
                }
            }
        }

        //Option fields
        $params['fields'] = $fields;

        //option size
        $params['size'] = 20;

        //Option minDate
        try {
            $params['date_min_prelevement'] = $this->minDate;
        } catch (\Throwable $th) {
            $io->error('Invalid syntax for "minDate" => dd-mm-YYYY or YYYY-mm-dd');
            exit();
        }

        //Option maxDate
        try {
            $params['date_max_prelevement'] = $this->maxDate;
        } catch (\Throwable $th) {
            $io->error('Invalid syntax for "minDate" => dd-mm-YYYY or YYYY-mm-dd');
            exit();
        }

        $data = $this->callApiService->getData('GET', 'https://hubeau.eaufrance.fr/api/vbeta/qualite_eau_potable/resultats_dis', $params);


        if ($input->getOption('saveMethod') == 'file') {
            //encode en json
            $data = json_encode($data);

            $result = $this->fileService->createFile($data);
            $io->success($result);

        } elseif ($input->getOption('saveMethod') == 'database') {
            //enregistrement en base de données
            foreach ($data as $key => $value) {
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

            $io->success('Data saved in database');

        } else {
            $io->error('Invalid syntax for "saveMethod" => file or database');
            exit();
        }
        return Command::SUCCESS;
    }


    /**
     * @throws Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $minDate = $input->getOption('minDate');
        $maxDate = $input->getOption('maxDate');
        $helper = $this->getHelper('question');

        $lastMonth = date('Y/m/d', strtotime('last month'));
        $now = date('Y/m/d', strtotime('now'));
  
        $minQuestion = new Question(sprintf('Provide the starting date [default: %s] : ' , $lastMonth),$lastMonth );
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
            $this->minDate = $this->setDate($minDate);
            $this->maxDate = $this->setDate($maxDate);
        } else 
            throw new Error("validation failed");
    }

    protected function validateDate($date , $format = "Y/m/d" )
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * @throws Exception
     */
    protected function setDate(string $date): DateTime
    {

        $date = new \DateTime($date);
        $date->format('Y/m/d H:i:s');
        return $date;
    }
}
