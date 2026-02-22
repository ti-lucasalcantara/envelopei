<?php

namespace App\Controllers;

use App\Models\Envelopei\FaturaModel;
use App\Models\Envelopei\EnvelopeModel;

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

    public function envelopeExtrato($id)
    {
        $uid = session('UsuarioId');
        if (!$uid) {
            return redirect()->to(base_url('login'));
        }

        $model = new EnvelopeModel();
        $env = $model->find((int)$id);

        if (!$env || (int)$env['UsuarioId'] !== (int)$uid) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Envelope não encontrado.');
        }

        $env['SaldoAtual'] = $model->saldoAtual((int)$id);

        return $this->view('envelopei/envelopes/extrato', [
            'titulo' => 'Extrato: ' . ($env['Nome'] ?? 'Envelope'),
            'envelope' => $env,
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

    public function cartoes()
    {
        return $this->view('envelopei/cartoes/index', [
            'titulo' => 'Envelopei - Cartões de Crédito',
        ]);
    }

    public function faturas()
    {
        return $this->view('envelopei/faturas/index', [
            'titulo' => 'Envelopei - Faturas',
        ]);
    }

    public function novaReceita()
    {
        return $this->view('envelopei/receitas/nova', [
            'titulo' => 'Nova Receita - Envelopei',
        ]);
    }

    public function novaDespesa()
    {
        return $this->view('envelopei/despesas/nova', [
            'titulo' => 'Nova Despesa - Envelopei',
        ]);
    }

    public function novaTransferencia()
    {
        return $this->view('envelopei/transferencias/nova', [
            'titulo' => 'Transferência entre Envelopes - Envelopei',
        ]);
    }

    public function faturaDetalhe($id)
    {
        $uid = session('UsuarioId');
        if (!$uid) {
            return redirect()->to(base_url('login'));
        }

        $model = new FaturaModel();
        $fatura = $model->find((int)$id);
        if (!$fatura) {
            return redirect()->to(base_url('faturas'))->with('error', 'Fatura não encontrada.');
        }

        $db = db_connect();
        $cartao = $db->table('tb_cartoes_credito')->where('CartaoCreditoId', $fatura['CartaoCreditoId'])->get()->getRowArray();
        if (!$cartao || (int)$cartao['UsuarioId'] !== (int)$uid) {
            return redirect()->to(base_url('faturas'))->with('error', 'Fatura não encontrada.');
        }

        $fatura['CartaoNome'] = $cartao['Nome'] ?? '';
        $fatura['Ultimos4Digitos'] = $cartao['Ultimos4Digitos'] ?? '';
        $fatura['Bandeira'] = $cartao['Bandeira'] ?? '';
        $fatura['Lancamentos'] = $model->lancamentosDaFatura((int)$id);
        $fatura['ValorPago'] = $model->valorPagoFatura((int)$id);
        $fatura['ValorRestante'] = max(0, (float)($fatura['ValorTotal'] ?? 0) - (float)($fatura['ValorPago'] ?? 0));

        return $this->view('envelopei/faturas/detalhe', [
            'titulo' => 'Fatura ' . ($fatura['CartaoNome'] ?? '') . ' - ' . str_pad($fatura['MesReferencia'] ?? 0, 2, '0', STR_PAD_LEFT) . '/' . ($fatura['AnoReferencia'] ?? ''),
            'fatura' => $fatura,
        ]);
    }
}
