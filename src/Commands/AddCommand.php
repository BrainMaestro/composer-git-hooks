<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use BrainMaestro\GitHooks\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    private $force;
    private $windows;
    private $noLock;
    private $ignoreLock;
    private $addedHooks = [];

    protected function configure()
    {
        $this
            ->setName('add')
            ->setDescription('Adds git hooks from the composer config')
            ->setHelp('This command allows you to add git hooks')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Override existing git hooks')
            ->addOption('no-lock', 'l', InputOption::VALUE_NONE, 'Do not create a lock file')
            ->addOption('ignore-lock', 'i', InputOption::VALUE_NONE, 'Add the lock file to .gitignore')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', '.git')
            ->addOption('force-win', null, InputOption::VALUE_NONE, 'Force windows bash compatibility')
        ;
    }

    protected function init($input)
    {
        $this->force = $input->getOption('force');
        $this->windows = $input->getOption('force-win') || is_windows();
        $this->noLock = $input->getOption('no-lock');
        $this->ignoreLock = $input->getOption('ignore-lock');
    }

    protected function command()
    {
        create_hooks_dir($this->gitDir);

        foreach ($this->hooks as $hook => $contents) {
            $this->addHook($hook, $contents);
        }

        if (! count($this->addedHooks)) {
            $this->error('No hooks were added. Try updating');
            return;
        }

        $this->addLockFile();
        $this->ignoreLockFile();
    }

    private function addHook($hook, $contents)
    {
        $filename = "{$this->gitDir}/hooks/{$hook}";
        $exists = file_exists($filename);

        // On windows, the shebang needs to point to bash
        // See: https://github.com/BrainMaestro/composer-git-hooks/issues/7
        $shebang = ($this->windows ? '#!/bin/bash' : '#!/bin/sh') . PHP_EOL . PHP_EOL;
        $contents = is_array($contents) ? implode(PHP_EOL, $contents) : $contents;

        if (! $this->force && $exists) {
            $this->comment("{$hook} already exists");
            return;
        }

        file_put_contents($filename, $shebang . $contents);
        chmod($filename, 0755);

        $operation = $exists ? 'Overwrote' : 'Added';
        $this->log("{$operation} <info>{$hook}</info> hook");

        $this->addedHooks[] = $hook;
    }

    private function addLockFile()
    {
        if ($this->noLock) {
            $this->comment('Skipped creating a '. Hook::LOCK_FILE . ' file');
            return;
        }

        file_put_contents(Hook::LOCK_FILE, json_encode($this->addedHooks));
        $this->comment('Created ' . Hook::LOCK_FILE . ' file');
    }

    private function ignoreLockFile()
    {
        if ($this->noLock) {
            return;
        }

        if (! $this->ignoreLock) {
            $this->comment('Skipped adding '. Hook::LOCK_FILE . ' to .gitignore');
            return;
        }

        $contents = file_get_contents('.gitignore');
        $return = strpos($contents, Hook::LOCK_FILE);

        if ($return === false) {
            file_put_contents('.gitignore', Hook::LOCK_FILE . PHP_EOL, FILE_APPEND);
            $this->comment('Added ' . Hook::LOCK_FILE . ' to .gitignore');
        }
    }
}
