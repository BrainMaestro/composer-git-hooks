<?php

namespace BrainMaestro\GitHooks\Commands;

use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
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
            ->setName('update')
            ->setDescription('Update git hooks specified in the composer config')
            ->setHelp('This command allows you to update git hooks')
            ->addOption('git-dir', 'g', InputOption::VALUE_REQUIRED, 'Path to git directory', '.git')
            ->addOption('force-win', null, InputOption::VALUE_NONE, 'Force windows bash compatibility')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitDir = $input->getOption('git-dir');
        $forceWindows = $input->getOption('force-win');
        $hookDir = "{$gitDir}/hooks";

        if (! is_dir($hookDir)) {
            mkdir($hookDir, 0700, true);
        }

        foreach ($this->hooks as $hook => $script) {
            $filename = "{$gitDir}/hooks/{$hook}";

            $operation = file_exists($filename) ? 'Updated' : 'Added';

            if ($forceWindows || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // On windows we need to add a SHEBANG
                // See: https://github.com/BrainMaestro/composer-git-hooks/issues/7
                $script = '#!/bin/bash' . PHP_EOL . $script;
            }

            file_put_contents($filename, $script);
            chmod($filename, 0755);
            $output->writeln("{$operation} <info>{$hook}</info> hook");
        }
    }
}
