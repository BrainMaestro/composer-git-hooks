<?php

use BrainMaestro\GitHooks\Hook;

trait PrepareHookTest
{
    public function setUp()
    {
        self::prepare();
    }

    public static function tearDownAfterClass()
    {
        self::prepare();
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

        passthru('sed -i "" /' . Hook::LOCK_FILE . '/d .gitignore');
    }
}
