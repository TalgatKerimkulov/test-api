<?php

declare(strict_types=1);

namespace App\Actions\File\Admin;

use App\Models\File;

class FileIndexAction
{
    /**
     * @return array<string,mixed>
     */
    public function handle(FileIndexActionData $input): array
    {
        $query = File::query()->orderByDesc('id');
        $total = (clone $query)->count();
        $items = $query
            ->offset(($input->page - 1) * $input->limit)
            ->limit($input->limit)
            ->get()
            ->map(fn (File $file): array => [
                'id' => $file->id,
                'name' => $file->original_name,
                'url' => $file->url(),
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'created_at' => $file->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $lastPage = (int) max(1, (int) ceil($total / $input->limit));

        return [
            'pagination' => [
                'total' => $total,
                'limit' => $input->limit,
                'current_page' => $input->page,
                'last_page' => $lastPage,
                'next_page' => $input->page < $lastPage ? $input->page + 1 : null,
                'prev_page' => $input->page > 1 ? $input->page - 1 : null,
            ],
            'items' => $items,
        ];
    }
}
