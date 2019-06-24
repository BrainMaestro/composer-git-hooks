<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use BrainMaestro\GitHooks\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('list-hooks')
            ->setDescription('List added hooks')
            ->setHelp('This command allows you to list your git hooks')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', git_dir())
            ->addOption('global', null, InputOption::VALUE_NONE, 'Perform hook command globally for every git repository')
        ;
    }

    protected function init($input)
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
