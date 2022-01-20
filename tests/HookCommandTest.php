<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\HookCommand;
use Symfony\Component\Console\Tester\CommandTester;

class HookCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_tests_hooks_that_exist()
    {
        foreach (self::$hooks as $hook => $script) {
            $command = new HookCommand($hook, $script, '.');
            $commandTester = new CommandTester($command);

            $commandTester->execute([]);
            $this->assertStringContainsString(str_replace('echo ', '', $script), $commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_terminates_if_previous_hook_fails()
    {
        $hook = [
            'pre-commit' => [
                'echo execution-error;exit 1',
                'echo before-commit'
            ],
        ];

        $command = new HookCommand('pre-commit', $hook['pre-commit'], '.');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $this->assertStringContainsString('execution-error', $commandTester->getDisplay());
        $this->assertStringNotContainsString('before-commit', $commandTester->getDisplay());
    }
}
