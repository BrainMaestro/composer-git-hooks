<?php

namespace BrainMaestro\GitHooks\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    private $output;

    protected $hooks;
    protected $gitDir;

    public function __construct($hooks)
    {
        $this->hooks = $hooks;
        parent::__construct();
    }

    abstract protected function init($input);
    abstract protected function command();

    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->gitDir = $input->getOption('git-dir');
        $this->init($input);
        $this->command();
    }

    protected function log($log)
    {
        $this->output->writeln($log);
    }

    protected function comment($comment)
    {
        $this->output->writeln("<comment>{$comment}</comment>");
    }

    protected function error($error)
    {
        $this->output->writeln("<error>{$error}</error>");
    }
}
