<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthEnvelopeiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1) Sessão
        $uid = session('UsuarioId');
        if (!empty($uid)) {
            return;
        }

        // 2) Header (futuro app)
        $hid = $request->getHeaderLine('X-Usuario-Id');
        if (!empty($hid) && ctype_digit($hid)) {
            // se quiser, pode popular a sessão aqui:
            // session()->set('UsuarioId', (int)$hid);
            return;
        }

        // Falhou: retorna JSON 401
        $response = service('response');

        return $response
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => 'Não autenticado.',
            ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nada
    }
}
