<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    private $output;

    protected $dir;
    protected $hooks;
    protected $gitDir;
    protected $global;
    protected $lockFile;

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
        $this->global = $input->getOption('global');
        $this->lockFile = Hook::LOCK_FILE;
        $this->dir = trim(
            $this->global && $this->gitDir === '.git'
                ? dirname(global_hook_dir())
                : $this->gitDir
        );

        if ($this->global) {
            $this->dir = trim(empty($this->dir) ? getenv('COMPOSER_HOME') : $this->dir);
            $this->hooks = Hook::getValidHooks($this->dir);
            $this->lockFile = $this->dir . '/' . Hook::LOCK_FILE;
        }

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
