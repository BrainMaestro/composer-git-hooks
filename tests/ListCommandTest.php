<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTester extends \PHPUnit_Framework_TestCase
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
    public function it_lists_hooks_that_exist()
    {
        $command = new ListCommand(array_flip(self::$hooks));
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        foreach (self::$hooks as $hook) {
            $this->assertContains($hook, $commandTester->getDisplay());
        }
    }

    public function tearDown()
    {
        foreach (self::$hooks as $hook) {
            unlink(".git/hooks/{$hook}");
        }
    }
}
