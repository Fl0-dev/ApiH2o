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
    protected function configure(): void
    {
        // à mettre à la suite de la commande dans le terminal
        $this
            ->addArgument('city', InputArgument::IS_ARRAY, 'city name or city code to import')
            ->addOption('param', null, InputOption::VALUE_IS_ARRAY| InputOption::VALUE_OPTIONAL, 'parameters codes to import')
            ->addOption('resultsByPage', null, InputOption::VALUE_OPTIONAL, 'number of results by page', 20)
            ->addOption('pageNumber', null, InputOption::VALUE_OPTIONAL, 'number of page', 1)
            ->addOption('minDate', null, InputOption::VALUE_OPTIONAL, 'first date ', '01/01/2022')
            ->addOption('MaxDate', null, InputOption::VALUE_OPTIONAL, 'last date', '01/04/2022')
            ->setHelp('This command is used to import data from the hb\'eau API.
It takes one or more city names or codes as parameters
and to for options:
  - one or more sampling parameter codes
  - the desired number of results per page
  - the desired page number
  - a start date of direct debit
  - a end date of direct debit')
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
