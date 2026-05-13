<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\File\Admin\FileCreateAction;
use App\Actions\File\Admin\FileCreateActionData;
use App\Actions\File\Admin\FileDeleteAction;
use App\Actions\File\Admin\FileDeleteActionData;
use App\Actions\File\Admin\FileIndexAction;
use App\Actions\File\Admin\FileIndexActionData;
use App\Actions\File\Admin\FileItemListAction;
use App\Actions\File\Admin\FileItemListActionData;
use App\Actions\File\Admin\FileShowAction;
use App\Actions\File\Admin\FileShowActionData;
use App\Actions\File\Admin\FileUpdateAction;
use App\Actions\File\Admin\FileUpdateActionData;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Presenters\ErrorViewModel;
use App\Presenters\File\FileViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function create(Request $request, FileCreateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileCreateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output, 201);
    }

    public function index(Request $request, FileIndexAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileIndexActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output);
    }

    public function show(Request $request, FileShowAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileShowActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output);
    }

    public function update(Request $request, FileUpdateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileUpdateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output);
    }

    public function delete(Request $request, FileDeleteAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileDeleteActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output);
    }

    public function itemList(Request $request, FileItemListAction $action): JsonResponse
    {
        try {
            $output = $action->handle(FileItemListActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return FileViewModel::present($output);
    }
}
