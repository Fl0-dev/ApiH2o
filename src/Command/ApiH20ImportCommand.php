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
            ->addArgument('city', InputArgument::IS_ARRAY, 'commune par nom ou code')
            ->addOption('param', null, InputOption::VALUE_IS_ARRAY| InputOption::VALUE_OPTIONAL, 'Paramètres de mesure')
            ->addOption('resultsByPage', null, InputOption::VALUE_OPTIONAL, 'Paramètres de mesure')
            ->addOption('param', null, InputOption::VALUE_OPTIONAL, 'Paramètres de mesure')
            ->addOption('param', null, InputOption::VALUE_OPTIONAL, 'Paramètres de mesure')
            ->addOption('param', null, InputOption::VALUE_OPTIONAL, 'Paramètres de mesure')
            ->setHelp('Possibilities description')
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
