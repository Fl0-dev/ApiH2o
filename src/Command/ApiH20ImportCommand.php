<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->addArgument($argumentCity, InputArgument::IS_ARRAY,'city name or city code to import')
            ->addOption($optionParam, null, InputOption::VALUE_IS_ARRAY| InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption($optionResultsByPage, null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption($optionPageNumber, null, InputOption::VALUE_OPTIONAL, 'number of page', 1)
            ->addOption($optionMinDate, null, InputOption::VALUE_OPTIONAL, 'first date ', '01/01/2022')
            ->addOption($optionMaxDate, null, InputOption::VALUE_OPTIONAL, 'last date', '01/04/2022')
            ->setHelp(<<<HELP
            This command <info>$commandName</info> is used to import data from the hb'eau API.
            
            PARAMETER :
                <info>$argumentCity</info> : city name or city code to import

            OPTIONS :
                <info>$optionParam</info> : one or more sampling parameter codes to import
                <info>$optionResultsByPage</info> : the desired number of results per page (default: 20)
                <info>$optionPageNumber</info> : the desired page number (default: 1)
                <info>$optionMinDate</info> : a start date of direct debit (default: 01/01/2022)
                <info>$optionMaxDate</info> : a end date of direct debit (default: 01/04/2022)
                
             EXAMPLE :
                <info>$commandName paris --param=6455 --param=6489 --resultsByPage=10 --pageNumber=2 --minDate=01/01/2020 --maxDate=01/04/2020</info>
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
}
