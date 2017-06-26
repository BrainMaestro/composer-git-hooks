<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    private $hooks;

    public function __construct($hooks)
    {
        $this->hooks = $hooks;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('list-hooks')
            ->setDescription('List added hooks')
            ->setHelp('This command allows you to list your git hooks')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', '.git')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitDir = $input->getOption('git-dir');

        foreach (array_keys($this->hooks) as $hook) {
            $filename = "{$gitDir}/hooks/{$hook}";

            if (is_file($filename)) {
                $output->writeln("<info>{$hook}</info>");
            }
        }
    }
}
