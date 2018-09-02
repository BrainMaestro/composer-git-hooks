<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use BrainMaestro\GitHooks\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends Command
{
    private $force;
    private $lockFileHooks;
    private $hooksToRemove;

    protected function configure()
    {
        $this
            ->setName('remove')
            ->setDescription('Remove git hooks specified in the composer config')
            ->setHelp('This command allows you to remove git hooks')
            ->addArgument(
                'hooks',
                InputArgument::IS_ARRAY,
                'Hooks to be removed',
                array_keys($this->hooks)
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Delete hooks without checking the lock file'
            )
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', '.git')
        ;
    }

    protected function init($input)
    {
        $this->force = $input->getOption('force');
        $this->lockFileHooks = file_exists(Hook::LOCK_FILE)
            ? array_flip(json_decode(file_get_contents(Hook::LOCK_FILE)))
            : [];
        $this->hooksToRemove = $input->getArgument('hooks');
    }

    protected function command()
    {
        foreach ($this->hooksToRemove as $hook) {
            $filename = "{$this->gitDir}/hooks/{$hook}";

            if (! array_key_exists($hook, $this->lockFileHooks) && ! $this->force) {
                $this->comment("Skipped {$hook} hook - not present in lock file");
                continue;
            }

            if (array_key_exists($hook, $this->hooks) && is_file($filename)) {
                unlink($filename);
                $this->log("Removed <info>{$hook}</info> hook");
                unset($this->lockFileHooks[$hook]);
                continue;
            }

            $this->error("{$hook} hook does not exist");
        }

        file_put_contents(Hook::LOCK_FILE, json_encode(array_keys($this->lockFileHooks)));
    }
}
