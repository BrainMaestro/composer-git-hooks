<?php

use BrainMaestro\GitHooks\Commands\AddCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddCommandTester extends \PHPUnit_Framework_TestCase
{
    private static $hooks = [
        'test-pre-commit' => 'echo before-commit',
        'test-post-commit' => 'echo after-commit',
    ];

    /**
     * @test
     */
    public function it_adds_hooks_that_do_not_already_exist()
    {
        $command = new AddCommand(self::$hooks);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Added '{$hook}' hook", $commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_does_not_add_hooks_that_already_exist()
    {
        foreach (self::$hooks as $hook => $script) {
            file_put_contents(".git/hooks/{$hook}", $script);
        }

        $command = new AddCommand(self::$hooks);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("'{$hook}' already exists", $commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_adds_hooks_that_already_exist_if_forced_to()
    {
        $hook = array_rand(self::$hooks);
        $script = self::$hooks[$hook];
        file_put_contents(".git/hooks/{$hook}", $script);

        $command = new AddCommand(self::$hooks);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--force' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Added '{$hook}' hook", $commandTester->getDisplay());
        }
    }

    public function tearDown()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            unlink(".git/hooks/{$hook}");
        }
    }
}
