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
        $fs = new Filesystem();

        $output->writeln('Checking ...');

        if (!$fs->exists($this->container->getParameter('app.hook_dir') . '/git-web-hook')) {
            $output->writeln('There have been no updates');

            return;
        }

        $branch = file_get_contents($this->container->getParameter('app.hook_dir') . '/git-web-hook');

        if ($fs->exists($this->container->getParameter('app.hook_dir') . '/repository')) {
            $fs->remove($this->container->getParameter('app.hook_dir') . '/repository');
        }

        $output->writeln('Repository: ' . $this->container->getParameter('app.git_repo_link'));

        $process = new Process(sprintf(
            'git clone %1$s -b %2$s %3$s/repository',
            $this->container->getParameter('app.git_repo_link'),
            $branch,
            $this->container->getParameter('app.hook_dir')
        ));
        $process->start();
        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write($buffer);
            } else {
                $output->write($buffer);
            }
        });

        $commandFile = sprintf(
            '%s/repository%s',
            $this->container->getParameter('app.hook_dir'),
            $this->container->getParameter('app.command_file_path')
        );

        if (!$fs->exists($commandFile)) {
            $output->writeln('Command not found');

            return;
        }

        $process->setWorkingDirectory($this->container->getParameter('app.hook_dir') . '/repository');
        $process->setCommandLine(sprintf('git branch %1$s; git checkout %1$s', $branch));
        $process->run();
        $output->writeln($process->getOutput());
        $process->setCommandLine($commandFile);
        $process->start();
        $process->wait(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write($buffer);
            } else {
                $output->write($buffer);
            }
        });

        $fs->remove($this->container->getParameter('app.hook_dir') . '/repository');
        $fs->remove($this->container->getParameter('app.hook_dir') . '/git-web-hook');
    }
}