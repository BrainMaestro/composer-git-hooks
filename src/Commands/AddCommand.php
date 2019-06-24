<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use BrainMaestro\GitHooks\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    private $addedHooks = [];

    protected $force;
    protected $noLock;
    protected $windows;
    protected $ignoreLock;

    protected function configure()
    {
        $this
            ->setName('add')
            ->setDescription('Adds git hooks from the composer config')
            ->setHelp('This command allows you to add git hooks')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Override existing git hooks')
            ->addOption('no-lock', 'l', InputOption::VALUE_NONE, 'Do not create a lock file')
            ->addOption('ignore-lock', 'i', InputOption::VALUE_NONE, 'Add the lock file to .gitignore')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', git_dir())
            ->addOption('force-win', null, InputOption::VALUE_NONE, 'Force windows bash compatibility')
            ->addOption('global', null, InputOption::VALUE_NONE, 'Add global git hooks')
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
        if (empty($this->dir)) {
            $this->error('You did not specify a git directory to use');
            return;
        }

        create_hooks_dir($this->dir);

        foreach ($this->hooks as $hook => $contents) {
            $this->addHook($hook, $contents);
        }

        if (! count($this->addedHooks)) {
            $this->error('No hooks were added. Try updating');
            return;
        }

        $this->addLockFile();
        $this->ignoreLockFile();
        $this->setGlobalGitHooksPath();
    }

    protected function global_dir_fallback()
    {
        if (!empty($this->dir = trim(getenv('COMPOSER_HOME')))) {
            $this->dir = realpath($this->dir);
            $this->debug("No global git hook path was provided. Falling back to COMPOSER_HOME [{$this->dir}]");
        }
    }

    private function addHook($hook, $contents)
    {
        $filename = "{$this->dir}/hooks/{$hook}";
        $exists = file_exists($filename);

        // On windows, the shebang needs to point to bash
        // See: https://github.com/BrainMaestro/composer-git-hooks/issues/7
        $shebang = ($this->windows ? '#!/bin/bash' : '#!/bin/sh') . PHP_EOL . PHP_EOL;
        $contents = is_array($contents) ? implode(PHP_EOL, $contents) : $contents;

        if (! $this->force && $exists) {
            $this->debug("[{$hook}] already exists");
            return;
        }

        file_put_contents($filename, $shebang . $contents . PHP_EOL);
        chmod($filename, 0755);

        $operation = $exists ? 'Updated' : 'Added';
        $this->info("{$operation} [{$hook}] hook");

        $this->addedHooks[] = $hook;
    }

    private function addLockFile()
    {
        if ($this->noLock) {
            $this->debug("Skipped creating a [{$this->lockFile}] file");
            return;
        }

        file_put_contents(Hook::LOCK_FILE, json_encode($this->addedHooks));
        $this->debug("Created [{$this->lockFile}] file");
    }

    private function ignoreLockFile()
    {
        if ($this->noLock) {
            return;
        }

        if (! $this->ignoreLock) {
            $this->debug("Skipped adding [{$this->lockFile}] to .gitignore");
            return;
        }

        $contents = file_get_contents('.gitignore');
        $return = strpos($contents, $this->lockFile);

        if ($return === false) {
            file_put_contents('.gitignore', $this->lockFile . PHP_EOL, FILE_APPEND);
            $this->debug("Added [{$this->lockFile}] to .gitignore");
        }
    }

    private function setGlobalGitHooksPath()
    {
        if (! $this->global) {
            return;
        }

        $previousGlobalHookDir = global_hook_dir();
        $globalHookDir = trim(realpath("{$this->dir}/hooks"));

        if ($globalHookDir === $previousGlobalHookDir) {
            return;
        }

        $this->info(
            'About to modify global git hook path. '
            . ($previousGlobalHookDir !== ''
                ? "Previous value was [{$previousGlobalHookDir}]"
                : 'There was no previous value')
        );

        $exitCode = 0;
        passthru("git config --global core.hooksPath {$globalHookDir}", $exitCode);

        if ($exitCode !== 0) {
            $this->error("Could not set global git hook path.\n" .
            " Try running this manually 'git config --global core.hooksPath {$globalHookDir}'");
            return;
        }

        $this->info("Global git hook path set to [{$globalHookDir}]");
    }
}
