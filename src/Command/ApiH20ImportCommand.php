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

    protected $minDate;
    protected $maxDate;

    protected function configure(): void
    {
        // à mettre à la suite de la commande dans le terminal
        $this
            ->addArgument('city', InputArgument::IS_ARRAY, 'city name or city code to import')
            ->addOption('param', null, InputOption::VALUE_IS_ARRAY| InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption('resultsByPage', null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption('pageNumber', null, InputOption::VALUE_OPTIONAL, 'number of page', 1)
            ->addOption('minDate', null, InputOption::VALUE_OPTIONAL, 'first date format aaaa/mm/jj')
            ->addOption('MaxDate', null, InputOption::VALUE_OPTIONAL, 'last date format aaaa/mm/jj')
            ->setHelp('Import H20 data from API')
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
