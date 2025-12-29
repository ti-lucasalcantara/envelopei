<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --------------------------------------------------
// CONFIGURAÇÕES GERAIS
// --------------------------------------------------
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);


// --------------------------------------------------
// HOME (landing)
// --------------------------------------------------
$routes->get('/', 'Home::index');


// --------------------------------------------------
// WEB (VIEWS) – Envelopei
// páginas que consomem a API
// --------------------------------------------------
$routes->group('', ['namespace' => 'App\Controllers'], function ($routes) {

    // Auth (web)
    $routes->get('login', 'EnvelopeiWeb::login');
    $routes->get('sair',  'EnvelopeiWeb::logout'); // opcional (se quiser botão por link)

    // Páginas protegidas (se quiser filtrar aqui também)
    // Obs: você pode usar o mesmo filter "authEnvelopei" para proteger as views
    $routes->group('', ['filter' => 'authEnvelopei'], function ($routes) {
        $routes->get('dashboard',   'EnvelopeiWeb::dashboard');
        $routes->get('envelopes',   'EnvelopeiWeb::envelopes');
        $routes->get('contas',      'EnvelopeiWeb::contas');
        $routes->get('lancamentos', 'EnvelopeiWeb::lancamentos');
        $routes->get('rateios', 'EnvelopeiWeb::rateios');
    });
});


// --------------------------------------------------
// API – Envelopei
// Todas as rotas retornam JSON
// --------------------------------------------------
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // ---------------------------
    // AUTH / USUÁRIO
    // ---------------------------
    $routes->post('login', 'AuthController::login');

    // rotas protegidas
    $routes->group('', ['filter' => 'authEnvelopei'], function ($routes) {
        $routes->post('logout', 'AuthController::logout');
        $routes->get('usuario', 'AuthController::me');

        // CONTAS
        $routes->get('contas', 'ContaController::index');
        $routes->post('contas', 'ContaController::store');
        $routes->get('contas/(:num)', 'ContaController::show/$1');
        $routes->put('contas/(:num)', 'ContaController::update/$1');
        $routes->delete('contas/(:num)', 'ContaController::delete/$1');
        $routes->get('contas/(:num)/saldo', 'ContaController::saldo/$1');

        // ENVELOPES
        $routes->get('envelopes', 'EnvelopeController::index');
        $routes->post('envelopes', 'EnvelopeController::store');
        $routes->get('envelopes/(:num)', 'EnvelopeController::show/$1');
        $routes->put('envelopes/(:num)', 'EnvelopeController::update/$1');
        $routes->delete('envelopes/(:num)', 'EnvelopeController::delete/$1');
        $routes->get('envelopes/(:num)/extrato', 'EnvelopeController::extrato/$1');

        // CATEGORIAS
        $routes->get('categorias', 'CategoriaController::index');
        $routes->post('categorias', 'CategoriaController::store');
        $routes->put('categorias/(:num)', 'CategoriaController::update/$1');
        $routes->delete('categorias/(:num)', 'CategoriaController::delete/$1');

        // LANÇAMENTOS
        $routes->get('lancamentos', 'LancamentoController::index');
        $routes->get('lancamentos/(:num)', 'LancamentoController::show/$1');
        $routes->delete('lancamentos/(:num)', 'LancamentoController::delete/$1');

        // RECEITAS / DESPESAS
        $routes->post('receitas', 'ReceitaController::store');
        $routes->post('despesas', 'DespesaController::store');

        // TRANSFERÊNCIAS
        $routes->post('transferencias/envelopes', 'TransferenciaController::entreEnvelopes');
        $routes->post('transferencias/contas', 'TransferenciaController::entreContas');

        // DASHBOARD
        $routes->get('dashboard/resumo', 'DashboardController::resumo');

        // RATEIO PRÉ-DEFINIDO (MODELOS)
        $routes->get('rateios-modelo', 'RateioModeloController::index');                // lista modelos
        $routes->get('rateios-modelo/padrao', 'RateioModeloController::padrao');        // pega modelo padrão
        $routes->get('rateios-modelo/(:num)', 'RateioModeloController::show/$1');       // detalhe + itens
        $routes->post('rateios-modelo', 'RateioModeloController::store');              // cria modelo + itens
        $routes->put('rateios-modelo/(:num)', 'RateioModeloController::update/$1');    // atualiza modelo + itens
        $routes->delete('rateios-modelo/(:num)', 'RateioModeloController::delete/$1'); // desativa modelo
        $routes->post('rateios-modelo/(:num)/definir-padrao', 'RateioModeloController::definirPadrao/$1'); // set padrão

    });
});
