<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponder
{
    protected function success(mixed $data = null, string $message = 'Operasi berhasil.', int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function created(mixed $data = null, string $message = 'Data berhasil dibuat.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function notFound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Anda tidak memiliki akses untuk melakukan aksi ini.'): JsonResponse
    {
        return $this->error($message, 403);
    }
}
