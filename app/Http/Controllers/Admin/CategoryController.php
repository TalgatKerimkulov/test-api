<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Category\Admin\CategoryCreateAction;
use App\Actions\Category\Admin\CategoryCreateActionData;
use App\Actions\Category\Admin\CategoryDeleteAction;
use App\Actions\Category\Admin\CategoryDeleteActionData;
use App\Actions\Category\Admin\CategoryIndexAction;
use App\Actions\Category\Admin\CategoryIndexActionData;
use App\Actions\Category\Admin\CategoryItemListAction;
use App\Actions\Category\Admin\CategoryItemListActionData;
use App\Actions\Category\Admin\CategoryShowAction;
use App\Actions\Category\Admin\CategoryShowActionData;
use App\Actions\Category\Admin\CategoryUpdateAction;
use App\Actions\Category\Admin\CategoryUpdateActionData;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Presenters\Category\CategoryViewModel;
use App\Presenters\ErrorViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function create(Request $request, CategoryCreateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryCreateActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output, 201);
    }

    public function index(Request $request, CategoryIndexAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryIndexActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output);
    }

    public function show(Request $request, CategoryShowAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryShowActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output);
    }

    public function update(Request $request, CategoryUpdateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryUpdateActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output);
    }

    public function delete(Request $request, CategoryDeleteAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryDeleteActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output);
    }

    public function itemList(Request $request, CategoryItemListAction $action): JsonResponse
    {
        try {
            $output = $action->handle(CategoryItemListActionData::fromRequest($request));
        } catch (ApiException $exception) {
            return ErrorViewModel::present($exception->getMessage(), $exception->httpStatus(), $exception->errorCode());
        }

        return CategoryViewModel::present($output);
    }
}
