<?php

namespace Lib;

use FilesystemIterator as fIterator;

class File
{
    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Delete a file.
     *
     * @param  string  $path
     * @return bool
     */
    public static function delete($path)
    {
        if (static::exists($path)) return @unlink($path);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return void
     */
    public static function move($path, $target)
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return void
     */
    public static function copy($path, $target)
    {
        return copy($path, $target);
    }

    /**
     * Create a new directory.
     *
     * @param  string  $path
     * @param  int     $chmod
     * @return void
     */
    public static function mkdir($path, $chmod = 0777)
    {
        return ( ! is_dir($path)) ? mkdir($path, $chmod, true) : true;
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @param  bool    $preserve
     * @return void
     */
    public static function rmdir($directory, $preserve = false)
    {
        if ( ! is_dir($directory)) return;

        $items = new fIterator($directory);

        foreach ($items as $item) {
            if ($item->isDir()) {
                static::rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }

        unset($items);
        if ( ! $preserve) @rmdir($directory);
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string  $directory
     * @return void
     */
    public static function cleandir($directory)
    {
        return static::rmdir($directory, true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }
}