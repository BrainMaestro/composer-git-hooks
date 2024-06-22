<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Hook;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected static $hooks = [
        'pre-commit' => 'echo before-commit',
        'post-commit' => 'echo after-commit',
    ];

    private $tempTestDir;
    private $initialGlobalHookDir;

    final public function setUp(): void
    {
        $this->initialGlobalHookDir = global_hook_dir();
        $this->tempTestDir = 'cghooks-temp-' . bin2hex(random_bytes(5));

        mkdir($this->tempTestDir);
        chdir($this->tempTestDir);
        shell_exec('git init');
        shell_exec('git config commit.gpgsign false');
        shell_exec('git config user.email "cghooks@example.com"');
        shell_exec('git config user.name "Composer Git Hooks"');

        touch('.gitignore');
        self::createTestComposerFile();
        shell_exec('git add . && git commit -m "Initial commit"');

        $this->init();
    }

    final public function tearDown(): void
    {
        chdir('..');
        self::rmdir($this->tempTestDir);
        $this->restoreGlobalHookDir();
    }

    protected function init()
    {
    }

    public static function createHooks($gitDir = '.git', $createLockFile = false, $lockDir = false)
    {
        static::initializeHooks($gitDir, $createLockFile, $lockDir, self::$hooks);
    }

    public static function createCustomHooks($hooks, $createLockFile = false)
    {
        unset($hooks['config']);
        static::initializeHooks('.git', $createLockFile, false, $hooks);
    }

    public static function initializeHooks($gitDir, $createLockFile, $lockDir, $hooks)
    {
        create_hooks_dir($gitDir);

        foreach ($hooks as $hook => $script) {
            file_put_contents("{$gitDir}/hooks/{$hook}", $script);
        }

        if ($createLockFile) {
            $lockFile = ((false !== $lockDir) ? ($lockDir . '/') : ($gitDir === '.git' ? '' : $gitDir . '/')) . Hook::LOCK_FILE;
            file_put_contents($lockFile, json_encode(array_keys($hooks)));
        }
    }

    public static function createTestComposerFile($dir = '.', $hooks = [])
    {
        $hooks = empty($hooks) ? self::$hooks : $hooks;
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        file_put_contents("{$dir}/composer.json", json_encode([
            'extra' => [
                'hooks' => $hooks,
            ],
        ]));
    }

    public static function removeTestComposerFile($dir = '.')
    {
        unlink("{$dir}/composer.json");
    }

    /**
     * Since PHP does not support the recursive deletion of
     * a directory and its entire contents we need a helper here.
     *
     * @see https://stackoverflow.com/a/3338133
     *
     * @param $dir string
     */
    public static function rmdir($dir)
    {
        if (is_dir($dir)) {
            $entries = scandir($dir);

            foreach ($entries as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    $path = "{$dir}/{$entry}";
                    if (is_dir($path)) {
                        self::rmdir($path);
                    } else {
                        unlink($path);
                    }
                }
            }

            rmdir($dir);
        }
    }

    private function restoreGlobalHookDir()
    {
        if (empty($this->initialGlobalHookDir)) {
            shell_exec('git config --global --unset core.hooksPath');
        } else {
            shell_exec("git config --global core.hooksPath {$this->initialGlobalHookDir}");
        }

        $this->assertEquals($this->initialGlobalHookDir, global_hook_dir());
    }
}
