<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\AddCommand;
use BrainMaestro\GitHooks\Hook;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use PHPUnit\Framework\Attributes\Test;

class AddCommandTest extends TestCase
{
    private $commandTester;

    public function init()
    {
        $this->commandTester = new CommandTester(new AddCommand());
    }

    /** @test  */
    #[Test]
    public function it_adds_hooks_that_do_not_already_exist()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /** @test  */
    #[Test]
    public function it_doesnt_allow_to_add_custom_hooks_by_default()
    {
        $customHooks = [
            'pre-flow-feature-start' => 'echo custom-hook',
        ];

        self::createTestComposerFile('.', $customHooks);

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString(
            "No hooks were added. Try updating",
            $this->commandTester->getDisplay()
        );
    }

    /** @test  */
    #[Test]
    public function it_allows_to_add_custom_hooks_specified_in_config_section()
    {
        $customHooks = [
            'config' => [
                'custom-hooks' => ['pre-flow-feature-start'],
            ],
            'pre-flow-feature-start' => 'echo "pre-flow-feature-start"',
            'pre-flow-hotfix-start' => 'echo "pre-flow-hotfix-start"',
        ];

        self::createTestComposerFile('.', $customHooks);

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString(
            'Added pre-flow-feature-start hook',
            $this->commandTester->getDisplay()
        );
        $this->assertStringNotContainsString(
            'Added pre-flow-hotfix-start hook',
            $this->commandTester->getDisplay()
        );
    }

    /** @test  */
    #[Test]
    public function it_adds_shebang_to_hooks_on_windows()
    {
        if (! is_windows()) {
            $this->markTestSkipped('This test is only relevant on windows. You\'re running Linux.');
        }

        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
        }
    }

    /** @test  */
    #[Test]
    public function it_does_not_add_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("{$hook} already exists", $this->commandTester->getDisplay());
        }

        $this->assertStringContainsString('No hooks were added. Try updating', $this->commandTester->getDisplay());
    }

    /** @test  */
    #[Test]
    public function it_detects_existing_correct_hooks()
    {
        $originalHooks = self::$hooks;
        self::$hooks = [
            'pre-commit' => '#!/bin/sh' . PHP_EOL . PHP_EOL . 'echo before-commit' . PHP_EOL,
            'post-commit' => '#!/bin/sh' . PHP_EOL . PHP_EOL . 'echo after-commit' . PHP_EOL,
        ];

        self::createHooks();
        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("{$hook} is up to date", $this->commandTester->getDisplay());
        }
        $this->assertStringContainsString('All hooks are up to date', $this->commandTester->getDisplay());

        self::$hooks = $originalHooks;
    }

    /** @test  */
    #[Test]
    public function it_overrides_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute(['--force' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /** @test  */
    #[Test]
    public function it_correctly_creates_the_hook_lock_file()
    {
        $currentDir = realpath(getcwd());
        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString('Created ' . $currentDir . '/' . Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertFileExists(Hook::LOCK_FILE);
        $this->assertEquals(json_encode(array_keys(self::$hooks)), file_get_contents(Hook::LOCK_FILE));
    }

    /**
     * @test
     * @group lock-dir
     */
    #[Test]
    #[Group('lock-dir')]
    public function it_correctly_creates_the_hook_lock_file_in_lock_dir()
    {
        $lockDir = 'lock-dir';
        $currentDir = realpath(getcwd());
        if (!file_exists($lockDir)) {
            mkdir('../' . $lockDir);
        }

        $hookFile = $currentDir . '/../' . $lockDir . '/' . Hook::LOCK_FILE;
        $this->commandTester->execute(['--lock-dir' => dirname($hookFile)], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString('Created '. $hookFile . ' file', $this->commandTester->getDisplay());
        $this->assertFileExists($hookFile);
        $this->assertEquals(json_encode(array_keys(self::$hooks)), file_get_contents($hookFile));
        self::rmdir('../' . $lockDir);
    }

    /** @test  */
    #[Test]
    public function it_does_not_create_the_hook_lock_file_if_the_no_lock_option_is_passed()
    {
        $currentDir = realpath(getcwd());
        $this->commandTester->execute(['--no-lock' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString('Skipped creating a ' . Hook::LOCK_FILE . ' file', $this->commandTester->getDisplay());
        $this->assertFileDoesNotExist(Hook::LOCK_FILE);
    }

    /** @test  */
    #[Test]
    public function it_does_not_ignore_the_hook_lock_file()
    {
        $currentDir = realpath(getcwd());
        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString('Skipped adding ' . Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        $this->assertFalse(strpos(file_get_contents('.gitignore'), Hook::LOCK_FILE));
    }

    /** @test  */
    #[Test]
    public function it_ignores_the_hook_lock_file_if_the_ignore_lock_option_is_passed()
    {
        $this->commandTester->execute(['--ignore-lock' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringContainsString('Added ' . Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        $this->assertTrue(strpos(file_get_contents('.gitignore'), Hook::LOCK_FILE) !== false);
    }

    /** @test  */
    #[Test]
    public function it_does_not_ignore_the_hook_lock_file_if_it_is_already_ignored()
    {
        file_put_contents('.gitignore', Hook::LOCK_FILE . PHP_EOL, FILE_APPEND);
        $this->commandTester->execute(['--ignore-lock' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->assertStringNotContainsString('Added ' . Hook::LOCK_FILE . ' to .gitignore', $this->commandTester->getDisplay());
        $this->assertTrue(strpos(file_get_contents('.gitignore'), Hook::LOCK_FILE) !== false);
    }

    /** @test  */
    #[Test]
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';
        $hookDir = "{$gitDir}/hooks";

        create_hooks_dir($gitDir);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$hookDir}/{$hook}");
        }
    }

    /** @test  */
    #[Test]
    public function it_does_not_create_a_lock_file_when_no_hooks_were_added()
    {
        self::removeTestComposerFile(); // so that there will be no hooks to add
        $this->commandTester->execute([]);

        $this->assertStringContainsString('No hooks were added. Try updating', $this->commandTester->getDisplay());
        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileDoesNotExist(".git/hooks/{$hook}");
        }
    }

    /** @test  */
    #[Test]
    public function it_create_git_hooks_path_when_hooks_dir_not_exists()
    {
        $gitDir = 'test-git-dir';
        $hookDir = "{$gitDir}/hooks";
        $this->assertFalse(is_dir($hookDir));

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$hookDir}/{$hook}");
        }
    }

    /** @test  */
    #[Test]
    public function it_adds_win_bash_compat_if_the_force_windows_option_is_passed()
    {
        $this->commandTester->execute(['--force-win' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
        }
    }

    /** @test  */
    #[Test]
    public function it_handles_commands_defined_in_an_array()
    {
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
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertStringContainsString(implode(PHP_EOL, $scripts), $content);
        }
    }


    /** @test  */
    #[Test]
    public function it_uses_commands_sequence_for_configured_hooks_only()
    {
        $hooks = [
            'config' => [
                'stop-on-failure' => ['pre-commit'],
            ],
            'pre-commit' => [
                'echo "pre-commit 1"',
                'echo "pre-commit 2"',
                'echo "pre-commit 3"',
            ],
            'post-commit' => [
                'echo "post-commit 1"',
                'echo "post-commit 2"',
                'echo "post-commit 3"',
            ],
        ];
        self::createTestComposerFile('.', $hooks);

        $this->commandTester->execute([]);

        $content = file_get_contents(".git/hooks/pre-commit");
        $expected = 'echo "pre-commit 1" && \\'. PHP_EOL.
                'echo "pre-commit 2" && \\'. PHP_EOL.
                'echo "pre-commit 3"';
        $this->assertStringContainsString($expected, $content);

        $content = file_get_contents(".git/hooks/post-commit");
        $expected = 'echo "post-commit 1"'. PHP_EOL.
                'echo "post-commit 2"'. PHP_EOL.
                'echo "post-commit 3"';
        $this->assertStringContainsString($expected, $content);
    }

    /** @test  */
    #[Test]
    public function it_adds_global_git_hooks()
    {
        $gitDir = 'test-global-git-dir';
        $hookDir = "{$gitDir}/hooks";

        self::createTestComposerFile($gitDir);

        $this->commandTester->execute(
            ['--global' => true, '--git-dir' => $gitDir],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $hookDir = realpath("{$gitDir}/hooks");
        $this->assertStringContainsString(
            'About to modify global git hook path. There was no previous value',
            $this->commandTester->getDisplay()
        );
        $this->assertStringContainsString("Global git hook path set to {$hookDir}", $this->commandTester->getDisplay());
        $this->assertEquals($hookDir, global_hook_dir());
    }

    /** @test  */
    #[Test]
    public function it_adds_global_git_hooks_and_shows_previous_global_dir()
    {
        $gitDir = 'test-global-git-dir';
        $hookDir = "{$gitDir}/hooks";
        $previousHookDir = '/root/hooks';
        shell_exec("git config --global core.hooksPath {$previousHookDir}");

        self::createTestComposerFile($gitDir);

        $this->commandTester->execute(
            ['--global' => true, '--git-dir' => $gitDir],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $hookDir = realpath("{$gitDir}/hooks");
        $this->assertStringContainsString(
            "About to modify global git hook path. Previous value was {$previousHookDir}",
            $this->commandTester->getDisplay()
        );
        $this->assertStringContainsString("Global git hook path set to {$hookDir}", $this->commandTester->getDisplay());
        $this->assertEquals($hookDir, global_hook_dir());
    }

    /** @test  */
    #[Test]
    public function it_adds_global_git_hooks_and_does_not_change_global_dir_if_it_matches_new_value()
    {
        $gitDir = 'test-global-git-dir';
        create_hooks_dir($gitDir);
        $hookDir = realpath("{$gitDir}/hooks");
        shell_exec("git config --global core.hooksPath {$hookDir}");

        self::createTestComposerFile($gitDir);

        $this->commandTester->execute(
            ['--global' => true, '--git-dir' => $gitDir],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $this->assertStringNotContainsString(
            "About to modify global git hook path. Previous value was {$hookDir}",
            $this->commandTester->getDisplay()
        );
        $this->assertStringNotContainsString("Global git hook path set to {$hookDir}", $this->commandTester->getDisplay());
        $this->assertEquals($hookDir, global_hook_dir());
    }

    /** @test  */
    #[Test]
    public function it_falls_back_to_composer_home_if_no_global_hook_dir_is_provided()
    {
        $gitDir = 'test-global-composer-home-dir';
        $hookDir = "{$gitDir}/hooks";
        putenv("COMPOSER_HOME={$gitDir}");

        shell_exec('git config --global --unset core.hooksPath');

        self::createTestComposerFile($gitDir);

        $this->commandTester->execute(['--global' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileExists("{$hookDir}/{$hook}");
        }

        $gitDir = realpath('test-global-composer-home-dir');
        $hookDir = "{$gitDir}/hooks";
        $this->assertStringContainsString(
            "No global git hook path was provided. Falling back to COMPOSER_HOME {$gitDir}",
            $this->commandTester->getDisplay()
        );
        $this->assertStringContainsString(
            "About to modify global git hook path. There was no previous value",
            $this->commandTester->getDisplay()
        );
        $this->assertStringContainsString("Global git hook path set to {$hookDir}", $this->commandTester->getDisplay());
        $this->assertEquals($hookDir, global_hook_dir());
    }

    /** @test  */
    #[Test]
    public function it_fails_if_global_hook_dir_is_missing()
    {
        putenv('COMPOSER_HOME=');

        shell_exec('git config --global --unset core.hooksPath');

        $this->commandTester->execute(['--global' => true], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringNotContainsString("Updated {$hook} hook", $this->commandTester->getDisplay());
        }

        $this->assertStringContainsString(
            'You did not specify a git directory to use',
            $this->commandTester->getDisplay()
        );
    }

    /** @test  */
    #[Test]
    public function it_adds_hooks_correctly_in_a_git_worktree()
    {
        $currentDir = realpath(getcwd());
        shell_exec('git branch develop');
        mkdir('../worktree-test');
        shell_exec('git worktree add -b test ../worktree-test develop');
        chdir('../worktree-test');

        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Added {$hook} hook", $this->commandTester->getDisplay());
            $this->assertFileDoesNotExist(".git/hooks/{$hook}");
            $this->assertFileExists("{$currentDir}/.git/hooks/{$hook}");
        }

        chdir($currentDir);
        self::rmdir('../worktree-test');
    }
}
