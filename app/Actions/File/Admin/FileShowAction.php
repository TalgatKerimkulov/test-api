<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Exceptions\ApiException;
use App\Models\File;

class FileShowAction
{
    public function handle(FileShowActionData $input): array
    {
        $file = File::query()->find($input->id);
        if (! $file) {
            throw new ApiException('File not found', 404);
        }

        return [
            'id' => $file->id,
            'name' => $file->original_name,
            'path' => $file->path,
            'url' => $file->url(),
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'created_at' => $file->created_at?->toIso8601String(),
        ];
    }
}
