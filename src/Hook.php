<?php

namespace BrainMaestro\GitHooks;

class Hook
{
    private static $hooks;

    /**
     * Add valid git hooks.
     */
    public static function add($hook, $script)
    {
        if (array_key_exists($hook, self::getHooks())) {
            file_put_contents(".git/hooks/{$hook}", $script);
            echo "Added {$hook}";
        }
    }

    /**
     * Remove valid git hooks.
     */
    public static function remove($hook)
    {
        if (array_key_exists($hook, self::getHooks())) {
            unlink(".git/hooks/{$hook}");
            echo "Removed {$hook}";
        }
    }

    /**
     * Get all valid git hooks
     */
    private static function getHooks()
    {
        if (! isset(self::$hooks)) {
            self::$hooks = array_flip([
               'applypatch-msg',
               'commit-msg',
               'post-applypatch',
               'post-checkout',
               'post-commit',
               'post-merge',
               'post-receive',
               'post-rewrite',
               'post-update',
               'pre-applypatch',
               'pre-auto-gc',
               'pre-commit',
               'pre-push',
               'pre-rebase',
               'pre-receive',
               'prepare-commit-msg',
               'push-to-checkout',
               'update',
           ]);
        }

        return self::$hooks;
    }
}
