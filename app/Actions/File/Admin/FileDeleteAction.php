<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Exceptions\ApiException;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileDeleteAction
{
    public function handle(FileDeleteActionData $input): bool
    {
        $file = File::query()->find($input->id);
        if (! $file) {
            throw new ApiException('File not found', 404);
        }

        Storage::disk($file->disk)->delete($file->path);

        DB::transaction(function () use ($file): void {
            $file->delete();
        });

        return true;
    }
}
