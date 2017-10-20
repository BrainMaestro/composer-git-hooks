<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addedHooks = [];
        $gitDir = $input->getOption('git-dir');
        $forceWindows = $input->getOption('force-win');
        $hookDir = "{$gitDir}/hooks";

        if (! is_dir($hookDir)) {
            mkdir($hookDir, 0700, true);
        }

        foreach ($this->hooks as $hook => $script) {
            $filename = "{$gitDir}/hooks/{$hook}";

            if (file_exists($filename)) {
                $output->writeln("<comment>{$hook} already exists</comment>");
            } else {
                if ($forceWindows || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // On windows we need to add a SHEBANG
                    // See: https://github.com/BrainMaestro/composer-git-hooks/issues/7
                    $script = '#!/bin/bash' . PHP_EOL . $script;
                }

                file_put_contents($filename, $script);
                chmod($filename, 0755);
                $output->writeln("Added <info>{$hook}</info> hook");
                $addedHooks[] = $hook;
            }
        }

        if (! count($addedHooks)) {
            $output->writeln('<error>No hooks were added. Try updating</error>');
            return;
        }

        if ($input->getOption('no-lock')) {
            $output->writeln('<comment>Skipped creating a '. Hook::LOCK_FILE . ' file</comment?');
            return;
        }

        $this->addLockFile($addedHooks, $output);

        if (! $input->getOption('ignore-lock')) {
            $output->writeln('<comment>Skipped adding '. Hook::LOCK_FILE . ' to .gitignore</comment>');
            return;
        }

        $this->ignoreLockFile($output);
    }

    private function addLockFile($hooks, $output)
    {
        file_put_contents(Hook::LOCK_FILE, json_encode($hooks));
        $output->writeln('<comment>Created ' . Hook::LOCK_FILE . ' file</comment>');
    }

    private function ignoreLockFile($output)
    {
        $contents = file_get_contents('.gitignore');
        $return = strpos($contents, Hook::LOCK_FILE);

        if ($return === false) {
            file_put_contents('.gitignore', Hook::LOCK_FILE . PHP_EOL, FILE_APPEND);
            $output->writeln('<comment>Added ' . Hook::LOCK_FILE . ' to .gitignore</comment>');
        }
    }
}
