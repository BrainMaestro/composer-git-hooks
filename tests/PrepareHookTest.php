<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Hook;

trait PrepareHookTest
{
    private static $hooks = [
        'test-pre-commit' => 'echo before-commit',
        'test-post-commit' => 'echo after-commit',
    ];

    public function setUp()
    {
        self::prepare();
    }

    public static function tearDownAfterClass()
    {
        self::prepare();
    }

    public static function createHooks($gitDir = '.git')
    {
        foreach (self::$hooks as $hook => $script) {
            file_put_contents("{$gitDir}/hooks/{$hook}", $script);
        }
    }

    private static function prepare()
    {
        foreach (array_keys(self::$hooks) as $hook) {
            if (file_exists(".git/hooks/{$hook}")) {
                unlink(".git/hooks/{$hook}");
            }
        }

        if (file_exists(Hook::LOCK_FILE)) {
            unlink(Hook::LOCK_FILE);
        }

        $ignoreContents = file_get_contents('.gitignore');
        file_put_contents('.gitignore', str_replace(Hook::LOCK_FILE . PHP_EOL, '', $ignoreContents));
    }
}
