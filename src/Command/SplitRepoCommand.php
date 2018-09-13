<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class SplitRepoCommand extends Command
{
    private $gitRepoLink;

    public function __construct(ContainerInterface $container, string $name = null)
    {
        parent::__construct($name);

        $this->gitRepoLink = $container->getParameter('app.git_repo_link');
    }

    protected function configure()
    {
        $this
            ->setName('app:split-repo')
            ->setDescription('Check if there has been some changes on a GitHub repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $output->writeln('Checking ...');

        if (!$fs->exists('/var/git-web-hook')) {
            $output->writeln('There were no updates');
            return;
        }

        $branch = file_get_contents('/var/git-web-hook');

        $fs->remove('/var/git-web-hook');

        $process = new Process(sprintf('git clone %s -b %s /var/repository', $this->gitRepoLink, $branch));
        $process->run();

        $output->writeln($process->getErrorOutput());

        if (!$fs->exists('/var/repository/test-command')) {
            $output->writeln('Command not found');
            return;
        }

        $process = new Process('/var/repository/test-command '.$branch);
        $process->run();

        $fs->remove('/var/repository');

        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
    }
}