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
        self::createHooks();
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
            $this->assertContains("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    public function it_removes_removed_hooks_from_the_lock_file()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            $contents = file_get_contents('.gitignore');
            $return = strpos($contents, Hook::LOCK_FILE);

            $this->assertEquals(0, $return);

            $this->commandTester->execute(['hooks' => [$hook]]);
            $this->assertContains("Removed {$hook} hook", $this->commandTester->getDisplay());

            $contents = file_get_contents('.gitignore');
            $return = strpos($contents, Hook::LOCK_FILE);
            $this->assertFalse($return);
        }
    }

    /**
     * @test
     */
    public function it_removes_individual_hooks()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            $this->commandTester->execute(['hooks' => [$hook]]);
            $this->assertContains("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_does_not_remove_hooks_not_present_in_the_lock_file()
    {
        $hook = 'test-hook';
        $this->commandTester->execute(['hooks' => [$hook]]);
        $this->assertContains(
            "Skipped {$hook} hook - not present in lock file", $this->commandTester->getDisplay()
        );
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
        $this->assertContains("Removed {$hook} hook", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';

        mkdir("{$gitDir}/hooks", 0777, true);

        self::createHooks($gitDir);
        $this->assertFalse(self::isDirEmpty("{$gitDir}/hooks"));

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Removed {$hook} hook", $this->commandTester->getDisplay());
        }

        $this->assertTrue(self::isDirEmpty("{$gitDir}/hooks"));

        $this->recursive_rmdir($gitDir);
    }

    public function tearDown()
    {
        self::prepare();
    }

    private static function isDirEmpty($dir)
    {
        return count(scandir($dir)) == 2;
    }
}
