<?php

namespace BrainMaestro\GitHooks;

class Hook
{
    const LOCK_FILE = 'composer-git-hooks.lock';
    private static $hooks;

    /**
     * Get scripts section of the composer config file.
     *
     * @return array
     */
    public static function getValidHooks()
    {
        $contents = file_get_contents('composer.json');
        $json = json_decode($contents, true);
        $json['scripts'] = isset($json['scripts']) ? $json['scripts'] : [];
        $hooks = [];

        foreach ($json['scripts'] as $hook => $script) {
            if (array_key_exists($hook, self::getHooks())) {
                $hooks[$hook] = $script;
            }
        }

        return $hooks;
    }

    /**
     * Check if a hook is valid
     */
    public static function isValidHook($hook)
    {
        return array_key_exists($hook, self::getHooks());
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
