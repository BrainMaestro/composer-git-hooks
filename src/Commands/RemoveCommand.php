<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($input->getArgument('hooks') as $hook) {
            $filename = ".git/hooks/{$hook}";

            if (array_key_exists($hook, $this->hooks) && is_file($filename)) {
                unlink(".git/hooks/{$hook}");
                $output->writeln("Removed '{$hook}' hook");
            } else {
                $output->writeln("'{$hook}' hook does not exist");
            }
        }
    }
}
