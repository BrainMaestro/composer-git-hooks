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
