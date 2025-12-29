<?php

namespace App\Controllers;

class EnvelopeiWeb extends BaseController
{
    private function view(string $path, array $data = [])
    {
        $data['titulo'] = $data['titulo'] ?? 'Envelopei';
        return view($path, $data);
    }

    public function login()
    {
        // se já estiver logado, manda pro dashboard
        if (session('UsuarioId')) {
            return redirect()->to(base_url('dashboard'));
        }

        return $this->view('envelopei/auth/login', [
            'titulo' => 'Envelopei - Login',
        ]);
    }

    public function logout()
    {
        // opcional: rota web para "sair" via link
        session()->remove('UsuarioId');
        return redirect()->to(base_url('login'));
    }

    public function dashboard()
    {
        return $this->view('envelopei/dashboard/index', [
            'titulo' => 'Envelopei - Dashboard',
        ]);
    }

    public function envelopes()
    {
        return $this->view('envelopei/envelopes/index', [
            'titulo' => 'Envelopei - Envelopes',
        ]);
    }

    public function contas()
    {
        return $this->view('envelopei/contas/index', [
            'titulo' => 'Envelopei - Contas',
        ]);
    }

    public function lancamentos()
    {
        return $this->view('envelopei/lancamentos/index', [
            'titulo' => 'Envelopei - Lançamentos',
        ]);
    }

    public function rateios()
    {
        return $this->view('envelopei/rateios_modelo/index', [
            'titulo' => 'Envelopei - Rateio Pré-definido',
        ]);
    }

}
