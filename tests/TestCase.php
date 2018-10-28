<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Hook;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    const TEMP_TEST_DIR = 'cghooks-temp';

    protected static $hooks = [
        'pre-commit' => 'echo before-commit',
        'post-commit' => 'echo after-commit',
    ];

    private $initialGlobalHookDir;

    final public function setUp()
    {
        $this->initialGlobalHookDir = global_hook_dir();

        mkdir(self::TEMP_TEST_DIR);
        chdir(self::TEMP_TEST_DIR);
        touch('.gitignore');
        self::createTestComposerFile();

        $this->init();
    }

    final public function tearDown()
    {
        chdir('..');
        self::rmdir(self::TEMP_TEST_DIR);
        $this->restoreGlobalHookDir();
    }

    protected function init()
    {
    }

    public static function createHooks($gitDir = '.git', $createLockFile = false)
    {
        create_hooks_dir($gitDir);

        foreach (self::$hooks as $hook => $script) {
            file_put_contents("{$gitDir}/hooks/{$hook}", $script);
        }

        if ($createLockFile) {
            $lockFile = ($gitDir === '.git' ? '' : $gitDir . '/') . Hook::LOCK_FILE;
            file_put_contents($lockFile, json_encode(array_keys(self::$hooks)));
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
    private static function rmdir($dir)
    {
        if (is_dir($dir)) {
            $entries = scandir($dir);

            foreach ($entries as $entry) {
                if ($entry != "." && $entry != "..") {
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
