<?php

if (! function_exists('windows_os')) {
    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    function windows_os()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}

if (! function_exists('mkdir_if_not_exist')) {
    /**
     * Makes directory if not exists.
     *
     * @param  string  $dir
     * @param  int  $mode
     * @param  bool  $recursive
     *
     * @return void
     */
    function mkdir_if_not_exist($dir, $mode = 0700, $recursive = true)
    {
        if (! is_dir($dir)) {
            mkdir($dir, $mode, $recursive);
        }
    }
}
