<?php

if (! function_exists('create_hooks_dir')) {
    /**
     * Create hook directory if not exists.
     *
     * @param  string  $dir
     * @param  int  $mode
     * @param  bool  $recursive
     *
     * @return void
     */
    function create_hooks_dir($dir, $mode = 0700, $recursive = true)
    {
        if (! is_dir("{$dir}/hooks")) {
            mkdir("{$dir}/hooks", $mode, $recursive);
        }
    }
}

if (! function_exists('is_windows')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function is_windows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}

if (! function_exists('global_hook_dir')) {
    /**
     * Gets the global directory set for git hooks
     */
    function global_hook_dir()
    {
        return trim(shell_exec('git config --global core.hooksPath'));
    }
}

if (! function_exists('is_composer_dev_mode')) {
    /**
     * During a composer install or update process,
     * a variable named COMPOSER_DEV_MODE will be added to the environment.
     * If the command was run with the --no-dev flag,
     * this variable will be set to 0, otherwise it will be set to 1.
     *
     * @see https://getcomposer.org/doc/articles/scripts.md#defining-scripts
     */
    function is_composer_dev_mode()
    {
        return getenv('COMPOSER_DEV_MODE') === '1';
    }
}
