<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthEnvelopeiFilter implements FilterInterface
{
    /**
     * Valida a sessao do usuario e responde conforme o tipo da rota.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $uid = session('UsuarioId');
        if (!empty($uid)) {
            return;
        }

        $hid = $request->getHeaderLine('X-Usuario-Id');
        if (!empty($hid) && ctype_digit($hid)) {
            return;
        }

        $caminho = trim($request->getUri()->getPath(), '/');
        if (!str_starts_with($caminho, 'api')) {
            return redirect()->to(base_url('login'))->with('erro', 'Faça login para continuar.');
        }

        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'success' => false,
                'message' => 'Não autenticado.',
            ]);
    }

    /**
     * Nao executa nenhuma acao apos a resposta.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
