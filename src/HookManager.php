<?php

namespace BrainMaestro\GitHooks;

class HookManager
{
    /**
     * Install all valid git hooks.
     * @param $force bool
     */
    public static function install($force)
    {
        $scripts = self::getComposerScripts();

        foreach ($scripts as $hook => $script) {
            Hook::add($hook, $script, $force);
        }
    }

    /**
     * Uninstall all valid git hooks.
     */
    public static function uninstall($hooks)
    {
        if (! $hooks) {
            $hooks = array_keys(self::getComposerScripts());
        }

        foreach ($hooks as $hook) {
            Hook::remove($hook);
        }
    }

    /**
     * Get scripts section of the composer config file.
     *
     * @return array
     */
    private static function getComposerScripts()
    {
        $contents = file_get_contents('composer.json');
        $json = json_decode($contents, true);

        return $json['scripts'];
    }
}
