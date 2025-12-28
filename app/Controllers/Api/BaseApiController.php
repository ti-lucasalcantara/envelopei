<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class BaseApiController extends BaseController
{
    protected function getJson(): array
    {
        $json = $this->request->getJSON(true);
        return is_array($json) ? $json : [];
    }

    protected function ok($data = [], string $message = 'OK', int $code = 200)
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON([
                'success' => true,
                'message' => $message,
                'data'    => $data,
            ]);
    }

    protected function fail(string $message = 'Erro', $errors = [], int $code = 400)
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON([
                'success' => false,
                'message' => $message,
                'errors'  => $errors,
            ]);
    }

    protected function usuarioIdFromRequest(array $payload = []): ?int
    {
        // 1) sessão (quando sua view consome a API no mesmo domínio)
        $sid = session('UsuarioId');
        if (!empty($sid)) {
            return (int)$sid;
        }

        // 2) header (quando virar app externo)
        $hid = $this->request->getHeaderLine('X-Usuario-Id');
        if (!empty($hid) && ctype_digit($hid)) {
            return (int)$hid;
        }

        // 3) payload
        if (!empty($payload['UsuarioId'])) {
            return (int)$payload['UsuarioId'];
        }

        return null;
    }

    protected function requireUsuarioId(array $payload = []): ?int
    {
        $uid = $this->usuarioIdFromRequest($payload);
        return $uid ?: null;
    }

    protected function normalizeDate(?string $date): string
    {
        return !empty($date) ? $date : date('Y-m-d');
    }
}
