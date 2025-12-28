<?php

namespace App\Controllers\Api;

use App\Models\Envelopei\UsuarioModel;

class AuthController extends BaseApiController
{
    public function login()
    {
        $p = $this->getJson();

        $email = trim($p['Email'] ?? '');
        $senha = (string)($p['Senha'] ?? '');

        if ($email === '' || $senha === '') {
            return $this->fail('Informe Email e Senha.', [], 422);
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->buscarPorEmail($email);

        if (!$usuario || (int)($usuario['Ativo'] ?? 0) !== 1) {
            return $this->fail('Usuário inválido.', [], 401);
        }

        if (!password_verify($senha, $usuario['SenhaHash'])) {
            return $this->fail('Credenciais inválidas.', [], 401);
        }

        session()->set('UsuarioId', (int)$usuario['UsuarioId']);

        return $this->ok([
            'UsuarioId' => (int)$usuario['UsuarioId'],
            'Nome'      => $usuario['Nome'],
            'Email'     => $usuario['Email'],
        ], 'Login OK');
    }

    public function logout()
    {
        session()->remove('UsuarioId');
        return $this->ok([], 'Logout OK');
    }

    public function me()
    {
        $uid = $this->usuarioIdFromRequest();
        if (!$uid) return $this->fail('Não autenticado.', [], 401);

        $usuario = (new UsuarioModel())->find($uid);
        if (!$usuario) return $this->fail('Usuário não encontrado.', [], 404);

        unset($usuario['SenhaHash']);

        return $this->ok($usuario);
    }
}
