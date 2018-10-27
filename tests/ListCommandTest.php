<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Commands\ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{
    private $commandTester;

    public function init()
    {
        $this->commandTester = new CommandTester(new ListCommand());
    }

    /**
     * @test
     */
    public function it_lists_hooks_that_exist()
    {
        self::createHooks();
        $this->commandTester->execute([]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains($hook, $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_uses_a_different_git_path_if_specified()
    {
        $gitDir = 'test-git-dir';
        self::createHooks($gitDir);

        $this->commandTester->execute(['--git-dir' => $gitDir]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains($hook, $this->commandTester->getDisplay());
        }
    }

    /**
     * @test
     */
    public function it_lists_global_git_hooks()
    {
        $gitDir = 'test-global-git-dir';
        create_hooks_dir($gitDir);
        $hookDir = realpath("{$gitDir}/hooks");

        self::createHooks($gitDir);
        self::createTestComposerFile($gitDir);

        shell_exec("git config --global core.hooksPath {$hookDir}");
        $this->commandTester->execute(['--global' => true]);

        foreach (array_keys(self::$hooks) as $hook) {
            $this->assertContains($hook, $this->commandTester->getDisplay());
        }
    }
}
