<?php

namespace BrainMaestro\GitHooks;

use Exception;

class Hook
{
    const LOCK_FILE = 'cghooks.lock';
    const CONFIG_SECTIONS = ['stop-on-failure'];

    /**
     * Return hook contents
     *
     * @param string $dir
     * @param array|string $contents
     * @param string $hook
     *
     * @return string
     */
    public static function getHookContents($dir, $contents, $hook)
    {
        if (is_array($contents)) {
            $commandsSequence = self::stopHookOnFailure($dir, $hook);
            $separator = $commandsSequence ? ' && \\'.PHP_EOL : PHP_EOL;
            $contents = implode($separator, $contents);
        }

        return $contents;
    }

    /**
     * Get config section of the composer config file.
     *
     * @param  string $dir dir where to look for composer.json
     * @param  string $section config section to fetch in the composer.json
     *
     * @return array
     */
    public static function getConfig($dir, $section)
    {
        if (! in_array($section, self::CONFIG_SECTIONS)) {
            throw new Exception("Invalid config section [{$section}]. Available sections: ".implode(', ', self::CONFIG_SECTIONS).'.');
        }

        $composerFile = "{$dir}/composer.json";
        if (! file_exists($composerFile)) {
            return [];
        }

        $contents = file_get_contents($composerFile);
        $json = json_decode($contents, true);
        if (! isset($json['extra']['hooks']['config'][$section])) {
            return [];
        }

        return $json['extra']['hooks']['config'][$section];
    }

    /**
     * Check if the given hook is a sequence of commands.
     *
     * @param string $dir
     * @param string $hook
     * @return bool
     */
    public static function stopHookOnFailure($dir, $hook)
    {
        return in_array($hook, self::getConfig($dir, 'stop-on-failure'));
    }

    /**
     * Get scripts section of the composer config file.
     *
     * @param	$dir	string	dir where to look for composer.json
     *
     * @return array
     */
    public static function getValidHooks($dir)
    {
        return array_filter(static::getHooks($dir), function ($hook) {
            return self::isDefaultHook($hook);
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function getHooks($dir)
    {
        $composerFile = "{$dir}/composer.json";
        if (! file_exists($composerFile)) {
            return [];
        }

        $contents = file_get_contents($composerFile);
        $json = json_decode($contents, true);

        return array_merge(
            isset($json['scripts']) ? $json['scripts'] : [],
            isset($json['hooks']) ? $json['hooks'] : [],
            isset($json['extra']['hooks']) ? $json['extra']['hooks'] : []
        );
    }

    /**
     * Check if a hook is valid
     */
    private static function isDefaultHook($hook)
    {
        return array_key_exists($hook, self::getDefaultHooks());
    }

    /**
     * Get all default git hooks
     */
    private static function getDefaultHooks()
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
