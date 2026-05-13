<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Provider\Admin\ProviderCreateAction;
use App\Actions\Provider\Admin\ProviderCreateActionData;
use App\Actions\Provider\Admin\ProviderDeleteAction;
use App\Actions\Provider\Admin\ProviderDeleteActionData;
use App\Actions\Provider\Admin\ProviderIndexAction;
use App\Actions\Provider\Admin\ProviderIndexActionData;
use App\Actions\Provider\Admin\ProviderItemListAction;
use App\Actions\Provider\Admin\ProviderItemListActionData;
use App\Actions\Provider\Admin\ProviderShowAction;
use App\Actions\Provider\Admin\ProviderShowActionData;
use App\Actions\Provider\Admin\ProviderUpdateAction;
use App\Actions\Provider\Admin\ProviderUpdateActionData;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Presenters\ErrorViewModel;
use App\Presenters\Provider\ProviderViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function create(Request $request, ProviderCreateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderCreateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output, 201);
    }

    public function index(Request $request, ProviderIndexAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderIndexActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output);
    }

    public function show(Request $request, ProviderShowAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderShowActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output);
    }

    public function update(Request $request, ProviderUpdateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderUpdateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output);
    }

    public function delete(Request $request, ProviderDeleteAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderDeleteActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output);
    }

    public function itemList(Request $request, ProviderItemListAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ProviderItemListActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ProviderViewModel::present($output);
    }
}
