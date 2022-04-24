<?php

namespace App\Command;

use App\Service\Information;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OverviewCommand extends Command
{
    private $information;

    public function __construct(Information $information)
    {
        $this->information = $information;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this
            ->setName('app:overview')
            ->setDescription('Provides information about the number of users, posts and comments')
            ->addArgument('name', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        if(isset($name))
        {
            $output->writeln([
                'Overview of the user :',
                '======================',
                ''
            ]);

            $output->writeln($this->information->user($name));
        }

        else
        {
            $output->writeln([
                'Overview of the app :',
                '=====================',
                ''
            ]);

            $output->writeln($this->information->overview());
        }

        return Command::SUCCESS;
    }
}
