<?php

use BrainMaestro\GitHooks\Commands\RemoveCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveCommandTester extends \PHPUnit_Framework_TestCase
{
    private static $hooks = [
        'test-pre-commit',
        'test-post-commit',
    ];

    public function setUp()
    {
        foreach (self::$hooks as $hook) {
            file_put_contents(".git/hooks/{$hook}", 'get schwifty');
        }
    }

    /**
     * @test
     */
    public function it_removes_hooks_that_were_added()
    {
        $command = new RemoveCommand(array_flip(self::$hooks));
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        foreach (self::$hooks as $hook) {
            $this->assertContains("Removed '{$hook}' hook", $commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_removes_individual_hooks()
    {
        $command = new RemoveCommand(array_flip(self::$hooks));
        $commandTester = new CommandTester($command);
        
        foreach (self::$hooks as $hook) {
            $commandTester->execute(['hooks' => [$hook]]);
            $this->assertContains("Removed '{$hook}' hook", $commandTester->getDisplay());
        }
    }

    public function tearDown()
    {
        foreach (self::$hooks as $hook) {
            if (file_exists(".git/hooks/{$hook}")) {
                unlink(".git/hooks/{$hook}");
            }
        }
    }
}
