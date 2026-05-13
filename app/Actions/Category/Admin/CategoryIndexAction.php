<?php

declare(strict_types=1);

namespace App\Actions\Category\Admin;

use App\Models\Category;

class CategoryIndexAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(CategoryIndexActionData $input): array
    {
        $query = Category::query()->orderBy('id');
        if ($input->tree) {
            $query->whereNull('parent_id')->with('children.children.children');
        }

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
