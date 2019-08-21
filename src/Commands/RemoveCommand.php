<?php

namespace BrainMaestro\GitHooks\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
                'Hooks to be removed'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Delete hooks without checking the lock file'
            )
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory')
            ->addOption('lock-dir', null, InputOption::VALUE_REQUIRED, 'Path to lock file directory', getcwd())
            ->addOption('global', null, InputOption::VALUE_NONE, 'Remove global git hooks')
        ;
    }

    protected function init(InputInterface $input)
    {
        $this->force = $input->getOption('force');
        $this->lockFileHooks = file_exists($this->lockFile)
            ? array_flip(json_decode(file_get_contents($this->lockFile)))
            : [];
        $hooks = $input->getArgument('hooks');
        $this->hooksToRemove = empty($hooks) ? array_keys($this->hooks) : $hooks;
    }

    protected function command()
    {
        foreach ($this->hooksToRemove as $hook) {
            $filename = "{$this->dir}/hooks/{$hook}";

            if (! array_key_exists($hook, $this->lockFileHooks) && ! $this->force) {
                $this->info("Skipped [{$hook}] hook - not present in lock file");
                $this->lockFileHooks = file_exists($this->lockFile)
                    ? array_flip(json_decode(file_get_contents($this->lockFile)))
                    : [];
                continue;
            }

            if (array_key_exists($hook, $this->hooks) && is_file($filename)) {
                unlink($filename);
                $this->info("Removed [{$hook}] hook");
                unset($this->lockFileHooks[$hook]);
                continue;
            }

            $this->error("{$hook} hook does not exist");
        }

        file_put_contents($this->lockFile, json_encode(array_keys($this->lockFileHooks)));
    }
}
