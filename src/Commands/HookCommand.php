<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HookCommand extends Command
{
    private $hook;
    private $script;

    public function __construct($hook, $script)
    {
        $this->hook = $hook;
        $this->script = $script;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName($this->hook)
            ->setDescription("Test your {$this->hook} hook")
            ->setHelp("This command allows you to test your {$this->hook} hook")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(shell_exec($this->script));
    }
}
