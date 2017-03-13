<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\AddCommand;
use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddCommandTester extends \PHPUnit_Framework_TestCase
{
    use PrepareHookTest;

    private $commandTester;

    public function setUp()
    {
        self::prepare();
        $command = new AddCommand(self::$hooks);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function it_adds_hooks_that_do_not_already_exist()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Added '{$hook}' hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_does_not_add_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("'{$hook}' already exists", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_correctly_creates_the_hook_lock_file()
    {
        $this->commandTester->execute([]);

        $this->assertContains('Created '. Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertTrue(file_exists(Hook::LOCK_FILE));
        $this->assertEquals(json_encode(array_keys(self::$hooks)), file_get_contents(Hook::LOCK_FILE));
    }

    /**
     * @test
     */
    public function it_does_not_create_the_hook_lock_file_if_the_no_lock_option_is_passed()
    {
        $this->commandTester->execute(['--no-lock' => true]);

        $this->assertContains('Skipped creating a '. Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertFalse(file_exists(Hook::LOCK_FILE));
    }

    /**
     * @test
     */
    public function it_does_not_ignore_the_hook_lock_file()
    {
        $this->commandTester->execute([]);

        $this->assertContains('Skipped adding '. Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        passthru('grep -q ' . Hook::LOCK_FILE . ' .gitignore', $return);
        $this->assertEquals(1, $return);
    }

    /**
     * @test
     */
    public function it_ignores_the_hook_lock_file_if_the_ignore_lock_option_is_passed()
    {
        $this->commandTester->execute(['--ignore-lock' => true]);

        $this->assertContains('Added ' . Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        passthru('grep -q ' . Hook::LOCK_FILE . ' .gitignore', $return);
        $this->assertEquals(0, $return);
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';
        passthru("mkdir -p {$gitDir}/hooks");
        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertTrue(file_exists("{$gitDir}/hooks/{$hook}"));
        }

        passthru("rm -rf {$gitDir}");
    }

    /**
     * @test
     */
    public function it_does_not_create_a_lock_file_when_no_hooks_were_added()
    {
        $commandTester = new CommandTester(new AddCommand([]));
        $commandTester->execute([]);

        $this->assertContains('No hooks were added. Try updating', $commandTester->getDisplay());
        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFalse(file_exists(".git/hooks/{$hook}"));
        }
    }
}
