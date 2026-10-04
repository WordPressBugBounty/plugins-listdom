<?php

class LSD_IX_File
{
    public static function resolve_upload($file, string $extension): ?string
    {
        if (!is_string($file) || $file === '') return null;
        if (strpbrk($file, '/\\') !== false || sanitize_file_name($file) !== $file) return null;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== $extension) return null;

        $directory = realpath((new LSD_Main())->get_upload_path());
        if ($directory === false) return null;

        $path = realpath($directory . DIRECTORY_SEPARATOR . $file);
        if ($path === false || dirname($path) !== $directory || !is_file($path)) return null;

        return $path;
    }
}
