<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Exceptions\ApiException;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUpdateAction
{
    public function handle(FileUpdateActionData $input): array
    {
        $model = File::query()->find($input->id);
        if (! $model) {
            throw new ApiException('File not found', 404);
        }

        if ($input->attachment !== null) {
            $disk = $input->disk ?? $model->disk;
            $extension = strtolower($input->attachment->getClientOriginalExtension());
            $filename = Str::uuid()->toString().($extension ? '.'.$extension : '');
            $path = trim("uploads/misc/{$filename}", '/');

            Storage::disk($disk)->put($path, file_get_contents($input->attachment->getRealPath()));
            Storage::disk($model->disk)->delete($model->path);

            DB::transaction(function () use ($model, $input, $disk, $path): void {
                $model->update([
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => $input->attachment?->getClientOriginalName() ?? $model->original_name,
                    'mime_type' => $input->attachment?->getClientMimeType() ?? $model->mime_type,
                    'size' => (int) ($input->attachment?->getSize() ?? $model->size),
                ]);
            });
        }

        return [
            'id' => $model->id,
            'name' => $model->original_name,
            'url' => $model->url(),
            'mime_type' => $model->mime_type,
            'size' => $model->size,
        ];
    }
}
