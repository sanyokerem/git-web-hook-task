<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class CheckGitRepositoryUpdatesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:split-repo')
            ->setDescription('Check if there was some changes on a GitHub repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $output->writeln('Checking ...');

        if (!$fs->exists('/var/git-web-hook')) {
            $output->writeln('There were no updates');
            return;
        }

        $fs->remove('/var/git-web-hook');

        $process = new Process('git clone https://github.com/sanyokerem/forma-pro-test.git /var/repository');
        $process->run();

        $process = new Process('/var/repository/test-command');
        $process->run();

        $fs->remove('/var/repository');

        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
    }
}