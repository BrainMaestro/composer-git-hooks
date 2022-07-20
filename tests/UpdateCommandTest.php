<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\UpdateCommand;
use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group update
 */
class UpdateCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    public function init()
    {
        $this->commandTester = new CommandTester(new UpdateCommand());
    }

    /**
     * @test
     */
    public function it_adds_hooks_that_do_not_already_exist()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_updates_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_adds_custom_hooks_that_do_not_already_exist()
    {
        $customHooks = [
            'config' => [
                'custom-hooks' => ['pre-flow-feature-start'],
            ],
            'pre-flow-feature-start' => 'echo "pre-flow-feature-start"',
        ];

        self::createTestComposerFile('.', $customHooks);

        $this->commandTester->execute([]);

        $this->assertStringContainsString("Added pre-flow-feature-start hook", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_updates_custom_hooks_that_already_exist()
    {
        $customHooks = [
            'config' => [
                'custom-hooks' => ['pre-flow-feature-start'],
            ],
            'pre-flow-feature-start' => 'echo "pre-flow-feature-start"',
        ];

        self::createTestComposerFile('.', $customHooks);
        self::createCustomHooks($customHooks, true);

        $this->commandTester->execute([]);

        $this->assertStringContainsString("Updated pre-flow-feature-start hook", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_adds_shebang_to_hooks_on_windows()
    {
        if (! is_windows()) {
            $this->markTestSkipped('This test is only relevant on windows. You\'re running Linux.');
        }

        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
        }
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';

        create_hooks_dir($gitDir);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$gitDir}/hooks/{$hook}");
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
            $this->assertFileExists("{$gitDir}/hooks/{$hook}");
        }
    }

    /**
     * @test
     */
    public function it_adds_win_bash_compat_if_the_force_windows_option_is_passed()
    {
        self::createHooks();
        $this->commandTester->execute(['--force-win' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());

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
        self::createHooks();
        $hooks = [
            'pre-commit' => [
                'echo pre-commit first',
                'echo pre-commit second',
                'echo pre-commit third',
            ],
        ];
        self::createTestComposerFile('.', $hooks);

        $this->commandTester->execute([]);

        foreach ($hooks as $hook => $scripts) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertStringContainsString(implode(PHP_EOL, $scripts), $content);
        }
    }

    /**
     * @test
     */
    public function it_updates_global_git_hooks()
    {
        $gitDir = 'test-global-git-dir';
        create_hooks_dir($gitDir);
        $hookDir = realpath("{$gitDir}/hooks");

        self::createHooks($gitDir);
        self::createTestComposerFile($gitDir);

        shell_exec("git config --global core.hooksPath {$hookDir}");
        $this->commandTester->execute(['--global' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     * @group lock-dir
     */
    public function it_updates_git_hooks_with_lock_dir()
    {
        $lockDir = realpath(getcwd()) . '/../lock-dir';
        if (!file_exists($lockDir)) {
            mkdir($lockDir);
        }
        $hookFile = $lockDir . '/' . Hook::LOCK_FILE;

        self::createHooks('.git', true, $lockDir);

        $this->commandTester->execute(['--lock-dir' => dirname($hookFile)]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }

        self::rmdir($lockDir);
    }

    /**
     * @test
     */
    public function it_fails_if_global_hook_dir_is_missing()
    {
        putenv('COMPOSER_HOME=');

        shell_exec('git config --global --unset core.hooksPath');

        $this->commandTester->execute(['--global' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringNotContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }

        $this->assertStringContainsString(
            'You need to run the add command globally first before you try to update',
            $this->commandTester->getDisplay()
        );
    }

    /**
     * @test
     */
    public function it_updates_hooks_correctly_in_a_git_worktree()
    {
        self::createHooks();
        $currentDir = realpath(getcwd());
        shell_exec('git branch develop');
        if (!file_exists($path = '../worktree-test')) {
            mkdir($path);
        }
        shell_exec('git worktree add -b test ../worktree-test develop');
        chdir('../worktree-test');

        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileDoesNotExist(".git/hooks/{$hook}");
            $this->assertFileExists("{$currentDir}/.git/hooks/{$hook}");
        }

        chdir($currentDir);
        self::rmdir('../worktree-test');
    }
}
