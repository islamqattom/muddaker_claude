<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

trait AvatarUpload
{
    protected function uploadAvatar(Request $request, string $fieldName = 'image'): ?string
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $file = $request->file($fieldName);
        $filename = Str::random(40) . '.' . $file->extension();
        $destinationPath = storage_path('app/public/avatars');

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if (!$file->move($destinationPath, $filename)) {
            throw new RuntimeException('Failed to move uploaded avatar file');
        }

        return 'avatars/' . $filename;
    }

    protected function deleteAvatar(?string $avatarPath): void
    {
        if (!$avatarPath) {
            return;
        }

        $fullPath = storage_path('app/public/' . $avatarPath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}
