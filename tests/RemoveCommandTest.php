<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\RemoveCommand;
use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveCommandTester extends \PHPUnit_Framework_TestCase
{
    use PrepareHookTest;

    private $commandTester;

    public function setUp()
    {
        foreach (self::$hooks as $hook => $script) {
            file_put_contents(".git/hooks/{$hook}", $script);
        }
        file_put_contents(Hook::LOCK_FILE, json_encode(array_keys(self::$hooks)));

        $command = new RemoveCommand(self::$hooks);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function it_removes_hooks_that_were_added()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Removed '{$hook}' hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_removes_individual_hooks()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            $this->commandTester->execute(['hooks' => [$hook]]);
            $this->assertContains("Removed '{$hook}' hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_does_not_remove_hooks_not_present_in_the_lock_file()
    {
        $hook = 'test-hook';
        $this->commandTester->execute(['hooks' => [$hook]]);
        $this->assertContains("Skipped '{$hook}' hook - not present in lock file", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_removes_hooks_not_present_in_the_lock_file_if_forced_to()
    {
        $hook = 'test-hook';
        file_put_contents(".git/hooks/{$hook}", 'get schwifty');
        $command = new RemoveCommand([$hook => 'get schwifty']);
        $this->commandTester = new CommandTester($command);

        $this->commandTester->execute(['hooks' => [$hook], '--force' => true]);
        $this->assertContains("Removed '{$hook}' hook", $this->commandTester->getDisplay());
    }

    public function tearDown()
    {
        self::prepare();
    }
}
