<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HookCommand extends SymfonyCommand
{
    private $hook;
    private $contents;
    private $composerDir;

    public function __construct($hook, $contents, $composerDir)
    {
        $this->hook     = $hook;
        $this->contents = $contents;
        $this->composerDir = $composerDir;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $contents = Hook::getHookContents($this->composerDir, $this->contents, $this->hook);
        $outputMessage = [];
        $returnCode    = 0;
        exec($contents, $outputMessage, $returnCode);

        $output->writeln(implode(PHP_EOL, $outputMessage));

        return $returnCode;
    }
}
