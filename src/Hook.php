<?php

namespace BrainMaestro\GitHooks;

class Hook
{
    const LOCK_FILE = 'cghooks.lock';

    /**
     * Get scripts section of the composer config file.
     *
     * @return array
     */
    public static function getValidHooks()
    {
        $contents = file_get_contents('composer.json');
        $json = json_decode($contents, true);
        $hooks = array_merge(
            isset($json['scripts']) ? $json['scripts'] : [],
            isset($json['hooks']) ? $json['hooks'] : [],
            isset($json['extra']['hooks']) ? $json['extra']['hooks'] : []
        );
        $validHooks = [];

        foreach ($hooks as $hook => $script) {
            if (array_key_exists($hook, self::getHooks())) {
                $validHooks[$hook] = $script;
            }
        }

        return $validHooks;
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
        return array_flip([
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
}
