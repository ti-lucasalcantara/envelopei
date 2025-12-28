<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session('UsuarioId')) {
            return redirect()->to(base_url('dashboard'));
        }

        return redirect()->to(base_url('login'));
    }
}
