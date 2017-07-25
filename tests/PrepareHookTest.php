<?php

namespace BrainMaestro\GitHooks\Tests;

use BrainMaestro\GitHooks\Hook;

trait PrepareHookTest
{
    private static $hooks = [
        'test-pre-commit' => 'echo before-commit',
        'test-post-commit' => 'echo after-commit',
    ];

    protected static $isWin = false;

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
        self::$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if (!is_dir("{$gitDir}/hooks")) {
            $command = "mkdir -p {$gitDir}/hooks";
            if (self::$isWin) {
                $command = "mkdir {$gitDir}\hooks";
            }
            passthru($command);
        }

        foreach (self::$hooks as $hook => $script) {
            file_put_contents("{$gitDir}/hooks/{$hook}", $script);
        }
    }

    private static function prepare()
    {
        self::$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        foreach (array_keys(self::$hooks) as $hook) {
            if (file_exists(".git/hooks/{$hook}")) {
                unlink(".git/hooks/{$hook}");
            }
        }

        if (file_exists(Hook::LOCK_FILE)) {
            unlink(Hook::LOCK_FILE);
        }

        $ignoreContents = file_get_contents('.gitignore');
        file_put_contents('.gitignore', str_replace(Hook::LOCK_FILE . ' '. PHP_EOL, '', $ignoreContents));
    }
}
