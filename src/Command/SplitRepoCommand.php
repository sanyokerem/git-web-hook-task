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
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container, string $name = null)
    {
        parent::__construct($name);

        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('app:split-repo')
            ->setDescription('Check if there has been some changes on a GitHub repository');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();

        $files = $finder->in($this->container->getParameter('app.hook_dir'))->files();

        $output->writeln('Checking ...');

        if (!$files->count()) {
            $output->writeln('There have been no updates');

            return;
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            var_dump($file->getFilename());
            if (preg_match('#^git-web-hook#uis', $file->getFilename())) {
                break;
            }

            $output->writeln('There have been no updates');

            return;
        }

        $branch = $file->getContents();

        $fs = new Filesystem();
        $fs->remove($file->getRealPath());
        unset($file);

        $repoPath = sprintf('%s/repository/%s', $this->container->getParameter('app.hook_dir'), $branch);

        if ($fs->exists($repoPath)) {
            $fs->remove($repoPath);
        }

        $output->writeln('Repository: ' . $this->container->getParameter('app.git_repo_link'));

        $process = new Process(sprintf(
            'git clone %s -b %s %s',
            $this->container->getParameter('app.git_repo_link'),
            $branch,
            $repoPath
        ));
        $process->start();
        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write($buffer);
            } else {
                $output->write($buffer);
            }
        });

        if ($process->getExitCode() > 0) {
            $output->writeln('Error occurred: ' . $process->getExitCodeText());

            return;
        }

        $process = new Process(sprintf('git branch %s; git checkout %s', $branch, $branch));
        $process->setWorkingDirectory($repoPath);
        $process->run();
        $output->writeln($process->getOutput());

        $commandFile = $repoPath . $this->container->getParameter('app.command_file_path');

        if (!$fs->exists($commandFile)) {
            $output->writeln('Command not found');

            return;
        }

        $process = new Process($commandFile);
        $process->setWorkingDirectory($repoPath);
        $process->start();
        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write($buffer);
            } else {
                $output->write($buffer);
            }
        });

        $fs->remove($repoPath);
    }
}