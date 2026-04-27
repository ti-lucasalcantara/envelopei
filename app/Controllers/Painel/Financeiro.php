<?php

namespace App\Controllers\Painel;

use App\Controllers\BaseController;
use App\Models\Envelopei\CategoriaModel;
use App\Models\Envelopei\ContaModel;
use App\Models\Envelopei\EnvelopeModel;
use App\Models\Envelopei\LancamentoModel;
use App\Services\Financeiro\ServicoLancamentos;
use App\Services\Financeiro\ServicoPainel;

class Financeiro extends BaseController
{
    /**
     * Exibe o dashboard financeiro consolidado.
     */
    public function dashboard()
    {
        $resumo = (new ServicoPainel())->resumo(usuario_logado_id());

        return view('envelopei/painel/dashboard', [
            'titulo' => 'Dashboard - Envelopei',
            'tituloPagina' => 'Dashboard',
            'subtituloPagina' => 'Resumo financeiro, envelopes, faturas e investimentos.',
            'resumo' => $resumo,
        ]);
    }

    /**
     * Lista contas financeiras do usuario.
     */
    public function contas()
    {
        return view('envelopei/contas/index', [
            'titulo' => 'Contas - Envelopei',
            'tituloPagina' => 'Contas',
            'subtituloPagina' => 'Contas bancarias, carteiras e corretoras.',
            'contas' => (new ServicoPainel())->listarContasComSaldo(usuario_logado_id(), false),
        ]);
    }

    /**
     * Exibe formulario para criar uma conta.
     */
    public function novaConta()
    {
        return view('envelopei/contas/formulario', [
            'titulo' => 'Nova conta - Envelopei',
            'tituloPagina' => 'Nova conta',
            'conta' => null,
        ]);
    }

    /**
     * Salva uma nova conta financeira.
     */
    public function salvarConta()
    {
        $dados = $this->request->getPost();
        $validacao = service('validation');
        $validacao->setRules([
            'Nome' => 'required|min_length[2]',
            'TipoConta' => 'required',
        ], [
            'Nome' => ['required' => 'Informe o nome da conta.'],
            'TipoConta' => ['required' => 'Informe o tipo da conta.'],
        ]);

        if (! $validacao->run($dados)) {
            return redirect()->back()->withInput()->with('errors', $validacao->getErrors());
        }

        (new ContaModel())->insert([
            'UsuarioId' => usuario_logado_id(),
            'Nome' => $dados['Nome'],
            'TipoConta' => $dados['TipoConta'],
            'Banco' => $dados['Banco'] ?? null,
            'SaldoInicial' => decimal_banco($dados['SaldoInicial'] ?? 0),
            'Ativa' => 1,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('contas'))->with('sucesso', 'Conta criada com sucesso.');
    }

    /**
     * Exibe formulario de edicao de conta.
     */
    public function editarConta(int $id)
    {
        $conta = $this->buscarRegistroUsuario(new ContaModel(), $id, 'UsuarioId');
        return view('envelopei/contas/formulario', [
            'titulo' => 'Editar conta - Envelopei',
            'tituloPagina' => 'Editar conta',
            'conta' => $conta,
        ]);
    }

    /**
     * Atualiza uma conta sem alterar seu historico financeiro.
     */
    public function atualizarConta(int $id)
    {
        $this->buscarRegistroUsuario(new ContaModel(), $id, 'UsuarioId');
        (new ContaModel())->update($id, [
            'Nome' => $this->request->getPost('Nome'),
            'TipoConta' => $this->request->getPost('TipoConta'),
            'Banco' => $this->request->getPost('Banco'),
            'SaldoInicial' => decimal_banco($this->request->getPost('SaldoInicial')),
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('contas'))->with('sucesso', 'Conta atualizada com sucesso.');
    }

    /**
     * Inativa uma conta preservando os lancamentos antigos.
     */
    public function inativarConta(int $id)
    {
        $this->buscarRegistroUsuario(new ContaModel(), $id, 'UsuarioId');
        (new ContaModel())->update($id, ['Ativa' => 0, 'DataAtualizacao' => date('Y-m-d H:i:s')]);
        return redirect()->to(base_url('contas'))->with('sucesso', 'Conta inativada com segurança.');
    }

    /**
     * Reativa uma conta inativa preservando todo o historico.
     */
    public function reativarConta(int $id)
    {
        $this->buscarRegistroUsuario(new ContaModel(), $id, 'UsuarioId');
        (new ContaModel())->update($id, ['Ativa' => 1, 'DataAtualizacao' => date('Y-m-d H:i:s')]);
        return redirect()->to(base_url('contas'))->with('sucesso', 'Conta reativada com sucesso.');
    }

    /**
     * Lista receitas ativas.
     */
    public function receitas()
    {
        return $this->listarLancamentos('receita', 'Receitas', 'Entradas recebidas e rateios aplicados.');
    }

    /**
     * Lista despesas ativas.
     */
    public function despesas()
    {
        return $this->listarLancamentos('despesa', 'Despesas', 'Saidas pagas, contas e envelopes vinculados.');
    }

    /**
     * Exibe formulario de nova receita.
     */
    public function novaReceita()
    {
        return $this->formularioLancamento('receita');
    }

    /**
     * Exibe formulario de nova despesa.
     */
    public function novaDespesa()
    {
        return $this->formularioLancamento('despesa');
    }

    /**
     * Exibe o formulario de edicao de uma receita.
     */
    public function editarReceita(int $id)
    {
        return $this->formularioLancamento('receita', $id);
    }

    /**
     * Exibe o formulario de edicao de uma despesa.
     */
    public function editarDespesa(int $id)
    {
        return $this->formularioLancamento('despesa', $id);
    }

    /**
     * Salva uma receita recebida.
     */
    public function salvarReceita()
    {
        try {
            (new ServicoLancamentos())->registrarReceita(usuario_logado_id(), $this->request->getPost());
            return redirect()->to(base_url('receitas'))->with('sucesso', 'Receita registrada com sucesso.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('erro', $e->getMessage());
        }
    }

    /**
     * Salva uma despesa paga.
     */
    public function salvarDespesa()
    {
        try {
            (new ServicoLancamentos())->registrarDespesa(usuario_logado_id(), $this->request->getPost());
            return redirect()->to(base_url('despesas'))->with('sucesso', 'Despesa registrada com sucesso.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('erro', $e->getMessage());
        }
    }

    /**
     * Atualiza uma receita existente sem recriar o historico.
     */
    public function atualizarReceita(int $id)
    {
        try {
            (new ServicoLancamentos())->atualizarLancamento(usuario_logado_id(), $id, $this->request->getPost());
            return redirect()->to(base_url('receitas'))->with('sucesso', 'Receita atualizada com sucesso.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('erro', $e->getMessage());
        }
    }

    /**
     * Atualiza uma despesa existente sem recriar o historico.
     */
    public function atualizarDespesa(int $id)
    {
        try {
            (new ServicoLancamentos())->atualizarLancamento(usuario_logado_id(), $id, $this->request->getPost());
            return redirect()->to(base_url('despesas'))->with('sucesso', 'Despesa atualizada com sucesso.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('erro', $e->getMessage());
        }
    }

    /**
     * Inativa receita ou despesa sem remover dados.
     */
    public function inativarLancamento(int $id)
    {
        try {
            (new ServicoLancamentos())->inativarLancamento(usuario_logado_id(), $id);
            return redirect()->back()->with('sucesso', 'Lançamento inativado com segurança.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('erro', $e->getMessage());
        }
    }

    /**
     * Reativa receita ou despesa sem recriar seus itens financeiros.
     */
    public function reativarLancamento(int $id)
    {
        try {
            (new ServicoLancamentos())->reativarLancamento(usuario_logado_id(), $id);
            return redirect()->back()->with('sucesso', 'Lançamento reativado com sucesso.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('erro', $e->getMessage());
        }
    }

    /**
     * Lista categorias do usuario.
     */
    public function categorias()
    {
        $categorias = (new CategoriaModel())
            ->where('UsuarioId', usuario_logado_id())
            ->orderBy('TipoCategoria', 'ASC')
            ->orderBy('Nome', 'ASC')
            ->findAll();

        return view('envelopei/categorias/index', [
            'titulo' => 'Categorias - Envelopei',
            'tituloPagina' => 'Categorias',
            'categorias' => $categorias,
        ]);
    }

    /**
     * Salva ou atualiza uma categoria.
     */
    public function salvarCategoria(?int $id = null)
    {
        $dados = [
            'UsuarioId' => usuario_logado_id(),
            'Nome' => $this->request->getPost('Nome'),
            'TipoCategoria' => $this->request->getPost('TipoCategoria') ?: 'ambos',
            'Cor' => $this->request->getPost('Cor'),
            'Icone' => $this->request->getPost('Icone') ?: 'ðŸ·ï¸',
            'Ativa' => 1,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ];

        $model = new CategoriaModel();
        if ($id) {
            $this->buscarRegistroUsuario($model, $id, 'UsuarioId');
            $model->update($id, $dados);
        } else {
            $model->insert($dados);
        }

        return redirect()->to(base_url('categorias'))->with('sucesso', 'Categoria salva com sucesso.');
    }

    /**
     * Lista envelopes com indicadores de meta.
     */
    public function envelopes()
    {
        return view('envelopei/envelopes/index', [
            'titulo' => 'Envelopes - Envelopei',
            'tituloPagina' => 'Envelopes',
            'subtituloPagina' => 'Alocacao logica do saldo das contas.',
            'envelopes' => (new ServicoPainel())->listarEnvelopesComSaldo(usuario_logado_id()),
            'contas' => (new ContaModel())->listarAtivas(usuario_logado_id()),
        ]);
    }

    /**
     * Salva um envelope novo ou existente.
     */
    public function salvarEnvelope(?int $id = null)
    {
        $model = new EnvelopeModel();
        $dados = [
            'UsuarioId' => usuario_logado_id(),
            'ContaId' => $this->request->getPost('ContaId') ?: null,
            'Nome' => $this->request->getPost('Nome'),
            'Descricao' => $this->request->getPost('Descricao'),
            'MetaValor' => decimal_banco($this->request->getPost('MetaValor')),
            'PercentualPadrao' => decimal_banco($this->request->getPost('PercentualPadrao')),
            'Cor' => $this->request->getPost('Cor') ?: '#0d6efd',
            'Icone' => $this->request->getPost('Icone') ?: 'ðŸ“¦',
            'Ativo' => 1,
            'DataAtualizacao' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->buscarRegistroUsuario($model, $id, 'UsuarioId');
            $model->update($id, $dados);
        } else {
            $model->insert($dados);
        }

        return redirect()->to(base_url('envelopes'))->with('sucesso', 'Envelope salvo com sucesso.');
    }

    /**
     * Busca um registro e garante que ele pertence ao usuario atual.
     */
    private function buscarRegistroUsuario($model, int $id, string $campoUsuario): array
    {
        $registro = $model->find($id);
        if (! $registro || (int) ($registro[$campoUsuario] ?? 0) !== usuario_logado_id()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Registro não encontrado.');
        }

        return $registro;
    }

    /**
     * Lista lancamentos de um tipo especifico.
     */
    private function listarLancamentos(string $tipo, string $titulo, string $subtitulo)
    {
        $lancamentos = db_connect()->table('tb_lancamentos l')
            ->select("
                l.*,
                c.Nome as CategoriaNome,
                (SELECT ic.Valor FROM tb_itens_conta ic WHERE ic.LancamentoId = l.LancamentoId ORDER BY ic.ItemContaId ASC LIMIT 1) as ValorConta,
                (SELECT ct.Nome FROM tb_itens_conta ic INNER JOIN tb_contas ct ON ct.ContaId = ic.ContaId WHERE ic.LancamentoId = l.LancamentoId ORDER BY ic.ItemContaId ASC LIMIT 1) as ContaNome,
                (SELECT ie.Valor FROM tb_itens_envelope ie WHERE ie.LancamentoId = l.LancamentoId AND ie.Valor <> 0 ORDER BY ie.ItemEnvelopeId ASC LIMIT 1) as ValorEnvelope,
                (SELECT e.Nome FROM tb_itens_envelope ie INNER JOIN tb_envelopes e ON e.EnvelopeId = ie.EnvelopeId WHERE ie.LancamentoId = l.LancamentoId AND ie.Valor <> 0 ORDER BY ie.ItemEnvelopeId ASC LIMIT 1) as EnvelopeNome
            ", false)
            ->join('tb_categorias c', 'c.CategoriaId = l.CategoriaId', 'left')
            ->where('l.UsuarioId', usuario_logado_id())
            ->where('l.TipoLancamento', $tipo)
            ->orderBy('COALESCE(l.Ativo, 1)', 'DESC', false)
            ->orderBy('l.DataLancamento', 'DESC')
            ->orderBy('l.LancamentoId', 'DESC')
            ->get()
            ->getResultArray();

        return view('envelopei/lancamentos/lista', [
            'titulo' => $titulo . ' - Envelopei',
            'tituloPagina' => $titulo,
            'subtituloPagina' => $subtitulo,
            'tipo' => $tipo,
            'lancamentos' => $lancamentos,
        ]);
    }

    /**
     * Monta o formulario compartilhado de receita e despesa.
     */
    private function formularioLancamento(string $tipo, ?int $lancamentoId = null)
    {
        $lancamento = null;
        if ($lancamentoId) {
            $lancamento = $this->carregarLancamentoParaEdicao($tipo, $lancamentoId);
        }

        return view('envelopei/lancamentos/formulario', [
            'titulo' => ($tipo === 'receita' ? 'Receita' : 'Despesa') . ' - Envelopei',
            'tituloPagina' => ($lancamentoId ? 'Editar ' : 'Nova ') . ($tipo === 'receita' ? 'receita' : 'despesa'),
            'tipo' => $tipo,
            'lancamento' => $lancamento,
            'contas' => (new ContaModel())->where('UsuarioId', usuario_logado_id())->orderBy('Ativa', 'DESC')->orderBy('Nome', 'ASC')->findAll(),
            'categorias' => (new CategoriaModel())->listarAtivas(usuario_logado_id()),
            'envelopes' => (new EnvelopeModel())->listarAtivos(usuario_logado_id()),
        ]);
    }

    /**
     * Carrega o lancamento e seus itens principais para preencher o formulario.
     */
    private function carregarLancamentoParaEdicao(string $tipo, int $lancamentoId): array
    {
        $lancamento = db_connect()->table('tb_lancamentos l')
            ->select('l.*, ic.ContaId, COALESCE(ABS(ic.Valor), ABS(ie.Valor), 0) as Valor, ie.EnvelopeId, ie.Valor as ValorEnvelope')
            ->join('tb_itens_conta ic', 'ic.LancamentoId = l.LancamentoId', 'left')
            ->join('tb_itens_envelope ie', 'ie.LancamentoId = l.LancamentoId AND ie.Valor <> 0', 'left')
            ->where('l.UsuarioId', usuario_logado_id())
            ->where('l.TipoLancamento', $tipo)
            ->where('l.LancamentoId', $lancamentoId)
            ->orderBy('ic.ItemContaId', 'ASC')
            ->orderBy('ie.ItemEnvelopeId', 'ASC')
            ->get()
            ->getRowArray();

        if (! $lancamento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Lançamento não encontrado.');
        }

        return $lancamento;
    }
}

