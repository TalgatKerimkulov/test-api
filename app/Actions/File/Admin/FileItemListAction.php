<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Models\File;

class FileItemListAction
{
    /**
     * @return array<int,array{id:int,name:string,url:?string}>
     */
    public function handle(FileItemListActionData $input): array
    {
        return File::query()
            ->when($input->search, fn ($q, $v) => $q->where('original_name', 'ilike', "%{$v}%"))
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (File $file): array => [
                'id' => $file->id,
                'name' => $file->original_name,
                'url' => $file->url(),
            ])
            ->values()
            ->all();
    }
}
