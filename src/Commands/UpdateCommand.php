<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use BrainMaestro\GitHooks\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    private $windows;

    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Update git hooks specified in the composer config')
            ->setHelp('This command allows you to update git hooks')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', '.git')
            ->addOption('force-win', null, InputOption::VALUE_NONE, 'Force windows bash compatibility')
        ;
    }

    protected function init($input)
    {
        $this->windows = $input->getOption('force-win') || is_windows();
    }

    protected function command()
    {
        create_hooks_dir($this->gitDir);

        foreach ($this->hooks as $hook => $contents) {
            $filename = "{$this->gitDir}/hooks/{$hook}";
            $operation = file_exists($filename) ? 'Updated' : 'Added';

            // On windows, the shebang needs to point to bash
            // See: https://github.com/BrainMaestro/composer-git-hooks/issues/7
            $shebang = ($this->windows ? '#!/bin/bash' : '#!/bin/sh') . PHP_EOL . PHP_EOL;
            $contents = is_array($contents) ? implode(PHP_EOL, $contents) : $contents;

            file_put_contents($filename, $shebang . $contents);
            chmod($filename, 0755);

            $this->log("{$operation} <info>{$hook}</info> hook");
        }
    }
}
