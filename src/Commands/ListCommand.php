<?php

namespace BrainMaestro\GitHooks\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('list-hooks')
            ->setDescription('List added hooks')
            ->setHelp('This command allows you to list your git hooks')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory')
            ->addOption('lock-dir', null, InputOption::VALUE_REQUIRED, 'Path to lock file directory', getcwd())
            ->addOption('global', null, InputOption::VALUE_NONE, 'Perform hook command globally for every git repository')
        ;
    }

    protected function init(InputInterface $input)
    {
    }

    protected function command()
    {
        foreach (array_keys($this->hooks) as $hook) {
            $filename = "{$this->dir}/hooks/{$hook}";

            if (is_file($filename)) {
                $this->info("[{$hook}]");
            }
        }
    }
}
