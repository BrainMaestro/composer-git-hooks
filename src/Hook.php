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
            $filename = ".git/hooks/{$hook}";
            file_put_contents($filename, $script);
            chmod($filename, 0755);
            echo "Added '{$hook}' hook" . PHP_EOL;
        }
    }

    /**
     * Remove valid git hooks.
     */
    public static function remove($hook)
    {
        if (array_key_exists($hook, self::getHooks())) {
            unlink(".git/hooks/{$hook}");
            echo "Removed '{$hook}' hook" . PHP_EOL;
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
