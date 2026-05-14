<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Client\ClientDeleteAction;
use App\Actions\Client\ClientDeleteActionData;
use App\Actions\Client\ClientIndexAction;
use App\Actions\Client\ClientIndexActionData;
use App\Actions\Client\ClientShowAction;
use App\Actions\Client\ClientShowActionData;
use App\Actions\Client\ClientStoreAction;
use App\Actions\Client\ClientStoreActionData;
use App\Actions\Client\ClientUpdateAction;
use App\Actions\Client\ClientUpdateActionData;
use App\Exceptions\ApiException;
use App\Presenters\Client\ClientViewModel;
use App\Presenters\ErrorViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ClientController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:clients.view,sanctum', only: ['index', 'show']),
            new Middleware('permission:clients.create,sanctum', only: ['store']),
            new Middleware('permission:clients.update,sanctum', only: ['update']),
            new Middleware('permission:clients.delete,sanctum', only: ['destroy']),
        ];
    }

    public function index(Request $request, ClientIndexAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ClientIndexActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ClientViewModel::presentCollection($output);
    }

    public function store(Request $request, ClientStoreAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ClientStoreActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ClientViewModel::presentItem($output, 201);
    }

    public function show(Request $request, ClientShowAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ClientShowActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ClientViewModel::presentItem($output);
    }

    public function update(Request $request, ClientUpdateAction $action): JsonResponse
    {
        try {
            $output = $action->handle(ClientUpdateActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ClientViewModel::presentItem($output);
    }

    public function destroy(Request $request, ClientDeleteAction $action): JsonResponse
    {
        try {
            $action->handle(ClientDeleteActionData::fromRequest($request));
        } catch (ApiException $e) {
            return ErrorViewModel::present($e->getMessage(), $e->httpStatus(), $e->errorCode());
        }

        return ClientViewModel::presentDeleted();
    }
}
