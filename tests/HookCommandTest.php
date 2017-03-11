<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\HookCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class HookCommandTester extends \PHPUnit_Framework_TestCase
{
    use PrepareHookTest;

    public function setUp() {}

    /**
     * @test
     */
    public function it_tests_hooks_that_exist()
    {
        foreach (self::$hooks as $hook => $script) {
            $command = new HookCommand($hook, $script);
            $commandTester = new CommandTester($command);

            $commandTester->execute([]);
            $this->assertContains(str_replace('echo ', '', $script), $commandTester->getDisplay());
        }
    }

    public function tearDown() {}
}
