<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\Api\ProductStoreAction;
use App\Actions\Product\Api\ProductUpdateAction;
use App\Actions\Product\Api\ProductUpsertActionData;
use App\Exceptions\RelationConflictException;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:products.create,sanctum', only: ['store']),
            new Middleware('permission:products.update,sanctum', only: ['update']),
            new Middleware('permission:products.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = (array) $request->input('filter', []);

        $products = Product::query()
            ->with('variations')
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->when($filters['provider_id'] ?? null, fn ($q, $v) => $q->whereHas('category', fn ($c) => $c->where('provider_id', $v)))
            ->when($filters['name'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%$v%"))
            ->orderBy('id')
            ->paginate((int) $request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request, ProductStoreAction $action): JsonResponse
    {
        $product = $action->handle(
            ProductUpsertActionData::fromValidated(
                validated: $request->validated(),
                variations: (array) $request->input('variations', []),
            ),
        );

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('variations'));
    }

    public function update(UpdateProductRequest $request, Product $product, ProductUpdateAction $action): ProductResource
    {
        $updated = $action->handle(
            ProductUpsertActionData::fromValidated(
                validated: $request->validated(),
                variations: $request->has('variations') ? (array) $request->input('variations', []) : null,
                product: $product,
            ),
        );

        return new ProductResource($updated);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->batchItems()->exists()) {
            throw new RelationConflictException(
                'product_in_use',
                'Product participates in purchases/orders and cannot be deleted.',
            );
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
