<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTester extends \PHPUnit_Framework_TestCase
{
    use PrepareHookTest;

    private $commandTester;

    public function setUp()
    {
        self::createHooks();
        $command = new ListCommand(self::$hooks);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function it_lists_hooks_that_exist()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains($hook, $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';

        $command = "mkdir -p {$gitDir}/hooks";
        if (self::$isWin) {
            $command = "mkdir {$gitDir}\hooks";
        }
        passthru($command);

        self::createHooks($gitDir);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains($hook, $this->commandTester->getDisplay());
        }

        $command = "rm -rf {$gitDir}";
        if (self::$isWin) {
            $command = "rd /s /q \"{$gitDir}\"";
        }
        passthru($command);
    }
}
