<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Product\Admin\ProductCreateAction;
use App\Actions\Product\Admin\ProductCreateActionData;
use App\Actions\Product\Admin\ProductDeleteAction;
use App\Actions\Product\Admin\ProductDeleteActionData;
use App\Actions\Product\Admin\ProductIndexAction;
use App\Actions\Product\Admin\ProductIndexActionData;
use App\Actions\Product\Admin\ProductItemListAction;
use App\Actions\Product\Admin\ProductItemListActionData;
use App\Actions\Product\Admin\ProductShowAction;
use App\Actions\Product\Admin\ProductShowActionData;
use App\Actions\Product\Admin\ProductUpdateAction;
use App\Actions\Product\Admin\ProductUpdateActionData;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Presenters\ErrorViewModel;
use App\Presenters\Product\ProductViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function create(Request $request, ProductCreateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductCreateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output, 201);
    }

    public function index(Request $request, ProductIndexAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductIndexActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output);
    }

    public function show(Request $request, ProductShowAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductShowActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output);
    }

    public function update(Request $request, ProductUpdateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductUpdateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output);
    }

    public function delete(Request $request, ProductDeleteAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductDeleteActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output);
    }

    public function itemList(Request $request, ProductItemListAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProductItemListActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProductViewModel::present($output);
    }
}
