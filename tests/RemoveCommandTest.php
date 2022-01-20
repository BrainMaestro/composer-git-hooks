<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\RemoveCommand;
use BrainMaestro\GitHooks\Hook;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group remove
 */
class RemoveCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    public function init()
    {
        self::createHooks('.git', true);
        $this->commandTester = new CommandTester(new RemoveCommand());
    }

    /**
     * @test
     */
    public function it_removes_hooks_that_were_added()
    {
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_removes_custom_hooks_that_were_added()
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

        $this->assertStringContainsString("Removed pre-flow-feature-start hook", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_removes_removed_hooks_from_the_lock_file()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            $contents = file_get_contents('.gitignore');
            $return = strpos($contents, Hook::LOCK_FILE);

            $this->assertEquals(0, $return);

            $this->commandTester->execute(['hooks' => [$hook]]);
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());

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
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_does_not_remove_hooks_not_present_in_the_lock_file()
    {
        $hook = 'pre-commit';
        unlink(Hook::LOCK_FILE);

        $this->commandTester->execute(['hooks' => [$hook]], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $this->assertStringContainsString(
            "Skipped {$hook} hook - not present in lock file",
            $this->commandTester->getDisplay()
        );
    }

    /**
     * @test
     */
    public function it_removes_hooks_not_present_in_the_lock_file_if_forced_to()
    {
        $hook = 'pre-commit';
        unlink(Hook::LOCK_FILE);
        touch(".git/hooks/{$hook}");

        $this->commandTester->execute(['hooks' => [$hook], '--force' => true]);
        $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';
        self::createHooks($gitDir, true);

        $this->assertFalse(self::isDirEmpty("{$gitDir}/hooks"));

        $this->commandTester->execute(['--git-dir' => $gitDir]);
        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
        }

        $this->assertTrue(self::isDirEmpty("{$gitDir}/hooks"));
    }

    /**
     * @test
     */
    public function it_removes_global_git_hooks()
    {
        $gitDir = 'test-global-git-dir';
        $hookDir = "{$gitDir}/hooks";

        self::createHooks($gitDir, true);
        self::createTestComposerFile($gitDir);
        $this->assertFalse(self::isDirEmpty($hookDir));

        shell_exec("git config --global core.hooksPath {$hookDir}");

        $this->commandTester->execute(['--global' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     * @group lock-dir
     */
    public function it_removes_git_hooks_with_lock_dir()
    {
        $lockDir = realpath(getcwd()) . '/../lock-dir';
        if (!file_exists($lockDir)) {
            mkdir($lockDir);
        }
        $hookFile = $lockDir . '/' . Hook::LOCK_FILE;
        self::createHooks('.git', true, $lockDir);

        $this->commandTester->execute(['--lock-dir' => dirname($hookFile)]);
        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertStringContainsString("Removed {$hook} hook", $this->commandTester->getDisplay());
        }
        self::rmdir($lockDir);
    }

    private static function isDirEmpty($dir)
    {
        return count(scandir($dir)) === 2;
    }
}
