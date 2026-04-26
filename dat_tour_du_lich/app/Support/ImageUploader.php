<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageUploader
{
    public static function store(UploadedFile $file, string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $targetDirectory = public_path($directory);

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $fileName = now()->format('YmdHis') . '-' . Str::random(10) . '.' . strtolower($extension);

        $file->move($targetDirectory, $fileName);

        return $directory . '/' . $fileName;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path(ltrim(str_replace('\\', '/', $path), '/'));

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
