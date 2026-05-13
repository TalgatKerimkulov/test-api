<?php

declare(strict_types=1);

namespace App\Actions\Provider\Admin;

use App\Models\Provider;

class ProviderIndexAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(ProviderIndexActionData $input): array
    {
        $query = Provider::query()
            ->when($input->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('id');

        $total = (clone $query)->count();
        $items = $query
            ->offset(($input->page - 1) * $input->limit)
            ->limit($input->limit)
            ->get()
            ->toArray();

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
