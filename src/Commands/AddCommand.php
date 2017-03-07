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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->hooks as $hook => $script) {
            $filename = ".git/hooks/{$hook}";

            if (is_file($filename) && ! $input->getOption('force')) {
                $output->writeln("'{$hook}' already exists");
            } else {
                file_put_contents($filename, $script);
                chmod($filename, 0755);
                $output->writeln("Added '{$hook}' hook");
            }
        }
    }
}
