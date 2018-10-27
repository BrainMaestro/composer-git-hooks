<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\AddCommand;
use BrainMaestro\GitHooks\Hook;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddCommandTester extends TestCase
{
    use PrepareHookTest;

    private $commandTester;

    public function setUp()
    {
        self::cleanup();
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
            $this->assertContains("Added {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_adds_shebang_to_hooks_on_windows()
    {
        if (! is_windows()) {
            $this->markTestSkipped('This test is only relevant on windows. You\'re running Linux.');
        }

        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Added {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
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
            $this->assertContains("{$hook} already exists", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_overrides_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute(['--force' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Updated {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_correctly_creates_the_hook_lock_file()
    {
        $this->commandTester->execute([]);

        $this->assertContains('Created '. Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertFileExists(Hook::LOCK_FILE);
        $this->assertEquals(json_encode(array_keys(self::$hooks)), file_get_contents(Hook::LOCK_FILE));
    }

    /**
     * @test
     */
    public function it_does_not_create_the_hook_lock_file_if_the_no_lock_option_is_passed()
    {
        $this->commandTester->execute(['--no-lock' => true]);

        $this->assertContains('Skipped creating a '. Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertFileNotExists(Hook::LOCK_FILE);
    }

    /**
     * @test
     */
    public function it_does_not_ignore_the_hook_lock_file()
    {
        $this->commandTester->execute([]);

        $this->assertContains('Skipped adding '. Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        $this->assertFalse(strpos(file_get_contents('.gitignore'), Hook::LOCK_FILE));
    }

    /**
     * @test
     */
    public function it_ignores_the_hook_lock_file_if_the_ignore_lock_option_is_passed()
    {
        $this->commandTester->execute(['--ignore-lock' => true]);

        $this->assertContains('Added ' . Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        $this->assertTrue(strpos(file_get_contents('.gitignore'), Hook::LOCK_FILE) !== false);
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';
        $hookDir = "{$gitDir}/hooks";

        create_hooks_dir($gitDir);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $this->recursive_rmdir($gitDir);
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
            $this->assertFileNotExists(".git/hooks/{$hook}");
        }
    }

    /**
     * @test
     */
    public function it_create_git_hooks_path_when_hooks_dir_not_exists()
    {
        $gitDir = 'test-git-dir';
        $hookDir = "{$gitDir}/hooks";

        if (file_exists($hookDir)) {
            rmdir($hookDir);
        }

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $this->recursive_rmdir($gitDir);
    }

    /**
     * @test
     */
    public function it_adds_win_bash_compat_if_the_force_windows_option_is_passed()
    {
        $this->commandTester->execute(['--force-win' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Added {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
        }
    }

    /**
     * @test
     */
    public function it_handles_commands_defined_in_an_array()
    {
        $hooks = [
            'test-pre-commit' => [
                'echo pre-commit first',
                'echo pre-commit second',
                'echo pre-commit third',
            ],
        ];

        $command = new AddCommand($hooks);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        foreach ($hooks as $hook => $scripts) {
            $this->assertContains("Added {$hook} hook", $commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertContains(implode(PHP_EOL, $scripts), $content);
        }
    }

    /**
     * @test
     */
    public function it_adds_global_git_hooks()
    {
        $hooks = [
            'pre-commit' => 'echo pre-commit',
            'pre-push' => 'echo pre-commit',
        ];
        $gitDir = '/tmp/test-global-git-dir';
        $hookDir = "{$gitDir}/hooks";
        $initialDir = global_hook_dir();

        create_hooks_dir($gitDir);
        file_put_contents("{$gitDir}/composer.json", json_encode([
            'extra' => [
                'hooks' => $hooks,
            ],
        ]));

        $this->commandTester->execute(['--global' => true, '--git-dir' => $gitDir]);

        foreach (array_keys($hooks) as $hook) {
            $this->assertContains("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $hookDir = realpath("{$gitDir}/hooks");
        $this->assertContains("Global git hook path set to {$hookDir}", $this->commandTester->getDisplay());
        $this->assertEquals($hookDir, global_hook_dir());

        shell_exec("git config --global core.hooksPath {$initialDir}");
        $this->recursive_rmdir($gitDir);
    }
}
