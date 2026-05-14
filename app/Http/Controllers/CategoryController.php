<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\Common\CategoryAction;
use App\Actions\Category\Common\CategoryStoreActionData;
use App\Actions\Category\Common\CategoryUpdateActionData;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:categories.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:categories.create,sanctum', only: ['store']),
            new Middleware('permission:categories.update,sanctum', only: ['update']),
            new Middleware('permission:categories.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request, CategoryAction $service): AnonymousResourceCollection
    {
        return CategoryResource::collection($service->index($request));
    }

    public function store(Request $request, CategoryAction $service): JsonResponse
    {
        $category = $service->store(CategoryStoreActionData::fromRequest($request)->validated);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Category $category, CategoryAction $service): CategoryResource
    {
        return new CategoryResource($service->show($category));
    }

    public function update(Request $request, Category $category, CategoryAction $service): CategoryResource
    {
        $input = CategoryUpdateActionData::fromRequest($request, $category);
        return new CategoryResource($service->update($input->category, $input->validated));
    }

    public function destroy(Category $category, CategoryAction $service): JsonResponse
    {
        $service->destroy($category);

        return response()->json(['message' => 'Category deleted']);
    }
}
