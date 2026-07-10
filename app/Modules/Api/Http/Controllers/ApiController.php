<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ApiController extends Controller
{
    /**
     * @param  array<string, mixed>|JsonResource|mixed  $data
     */
    protected function success(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        $payload = [
            'data' => $data instanceof JsonResource ? $data->resolve() : $data,
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    /**
     * @param  array<string, mixed>|JsonResource|mixed  $data
     */
    protected function created(mixed $data, string $message = 'Создано.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * @param  array<string, mixed>|JsonResource|mixed  $data
     */
    protected function accepted(mixed $data, string $message = 'Принято.'): JsonResponse
    {
        return $this->success($data, $message, 202);
    }

    /**
     * @param  LengthAwarePaginator<mixed>  $paginator
     */
    protected function paginated(LengthAwarePaginator $paginator, callable $resourceFactory, string $message = ''): JsonResponse
    {
        $items = $paginator->getCollection()->map($resourceFactory)->values()->all();

        $payload = [
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        return response()->json($payload);
    }

    protected function streamed(StreamedResponse $response): StreamedResponse
    {
        return $response;
    }
}
