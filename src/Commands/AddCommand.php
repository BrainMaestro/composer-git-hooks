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
            ->addOption('no-lock', 'n', InputOption::VALUE_NONE, 'Do not create a lock file')
            ->addOption('ignore-lock', 'i', InputOption::VALUE_NONE, 'Add the lock file to .gitignore')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $addedHooks = [];

        foreach ($this->hooks as $hook => $script) {
            $filename = ".git/hooks/{$hook}";

            if (is_file($filename) && ! $input->getOption('force')) {
                $output->writeln("'{$hook}' already exists");
            } else {
                file_put_contents($filename, $script);
                chmod($filename, 0755);
                $output->writeln("Added '{$hook}' hook");
                $addedHooks[] = $hook;
            }
        }

        if ($input->getOption('no-lock')) {
            $output->writeln('Skipped creating a '. Hook::LOCK_FILE . ' file');
            return;
        }

        $this->addLockFile($addedHooks, $output);

        if (! $input->getOption('ignore-lock')) {
            $output->writeln('Skipped adding '. Hook::LOCK_FILE . ' to .gitignore');
            return;
        }

        $this->ignoreLockFile($output);
    }

    private function addLockFile($hooks, $output)
    {
        file_put_contents(Hook::LOCK_FILE, json_encode($hooks));
        $output->writeln('Created ' . Hook::LOCK_FILE . ' file');
    }

    private function ignoreLockFile($output)
    {
        passthru('grep -q ' . Hook::LOCK_FILE . ' .gitignore', $return);
        if ($return !== 0) {
            passthru('echo ' . Hook::LOCK_FILE . ' >> .gitignore');
            $output->writeln('Added ' . Hook::LOCK_FILE . ' to .gitignore');
        }
    }
}
