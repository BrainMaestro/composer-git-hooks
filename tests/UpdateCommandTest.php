<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\UpdateCommand;
use BrainMaestro\GitHooks\Hook;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateCommandTester extends TestCase
{
    use PrepareHookTest;

    private $commandTester;

    public function setUp()
    {
        self::prepare();
        $command = new UpdateCommand(self::$hooks);
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
    public function it_updates_hooks_that_already_exist()
    {
        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Updated {$hook} hook", $this->commandTester->getDisplay());
        }
    }

    public function it_adds_shebang_to_hooks_on_windows()
    {
        if (! is_windows()) {
            $this->markTestSkipped('This test is only relevant on windows. You\'re running Linux.');
        }

        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Updated {$hook} hook", $this->commandTester->getDisplay());

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

        create_hooks_dir($gitDir, 0777);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertFileExists("{$gitDir}/hooks/{$hook}");
        }

        $this->recursive_rmdir($gitDir);
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
            unlink("{$gitDir}/hooks/{$hook}");
        }

        rmdir($hookDir);
    }

    /**
     * @test
     */
    public function it_adds_win_bash_compat_if_the_force_windows_option_is_passed()
    {
        self::createHooks();
        $this->commandTester->execute(['--force-win' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains("Updated {$hook} hook", $this->commandTester->getDisplay());

            $content = file_get_contents(".git/hooks/" . $hook);
            $this->assertNotFalse(strpos($content, "#!/bin/bash"));
            $this->assertEquals(strpos($content, "#!/bin/bash"), 0);
        }
    }
}
