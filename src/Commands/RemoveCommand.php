<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveCommand extends Command
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lockFileHooks = file_exists(Hook::LOCK_FILE)
                         ? array_flip(json_decode(file_get_contents(Hook::LOCK_FILE)))
                         : [];
        $gitDir = $input->getOption('git-dir');

        foreach ($input->getArgument('hooks') as $hook) {
            $filename = "{$gitDir}/hooks/{$hook}";

            if (! array_key_exists($hook, $lockFileHooks) && ! $input->getOption('force')) {
                $output->writeln("<comment>Skipped {$hook} hook - not present in lock file</comment>");
                continue;
            }

            if (array_key_exists($hook, $this->hooks) && is_file($filename)) {
                unlink($filename);
                $output->writeln("Removed <info>{$hook}</info> hook");
                unset($lockFileHooks[$hook]);
            } else {
                $output->writeln("<error>{$hook} hook does not exist</error>");
            }
        }

        file_put_contents(Hook::LOCK_FILE, json_encode(array_keys($lockFileHooks)));
    }
}
