<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {    
        foreach (array_keys(Hook::getValidHooks()) as $hook) {
            $filename = ".git/hooks/{$hook}";

            if (is_file($filename)) {
                $output->writeln($hook);
            }
        }
    }
}
