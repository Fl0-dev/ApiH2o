<?php

namespace App\Command;

use DateTime;
use Error;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        //output : écrire dans le terminal
        //input : récupérer les données de l'utilisateur
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $minDate = $input->getOption('minDate');
        $maxDate = $input->getOption('MaxDate');
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
