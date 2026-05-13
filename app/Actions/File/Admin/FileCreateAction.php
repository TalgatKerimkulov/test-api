<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileCreateAction
{
    public function handle(FileCreateActionData $input): array
    {
        $disk = $input->disk;
        $file = $input->attachment;
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid()->toString().($extension ? '.'.$extension : '');
        $path = trim("uploads/{$input->type}/{$filename}", '/');

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $created = DB::transaction(function () use ($disk, $path, $file): File {
            return File::query()->create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => (int) ($file->getSize() ?? 0),
            ]);
        });

        return [
            'id' => $created->id,
            'name' => $created->original_name,
            'path' => $created->path,
            'url' => $created->url(),
            'mime_type' => $created->mime_type,
            'size' => $created->size,
        ];
    }
}
