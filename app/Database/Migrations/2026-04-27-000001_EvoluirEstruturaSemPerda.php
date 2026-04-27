<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EvoluirEstruturaSemPerda extends Migration
{
    /**
     * Adiciona campos opcionais para a nova interface sem remover dados antigos.
     */
    public function up()
    {
        $this->adicionarColunasSeNaoExistirem('tb_usuarios', [
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_contas', [
            'Banco' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'TipoConta'],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_categorias', [
            'Cor' => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'Icone' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_envelopes', [
            'ContaId' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'UsuarioId'],
            'Descricao' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'Nome'],
            'MetaValor' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0, 'after' => 'Descricao'],
            'PercentualPadrao' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0, 'after' => 'MetaValor'],
            'Icone' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true, 'after' => 'Cor'],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_lancamentos', [
            'Ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'TipoLancamento'],
            'RecebidoPago' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'Ativo'],
            'Observacao' => ['type' => 'TEXT', 'null' => true, 'after' => 'Descricao'],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_cartoes_credito', [
            'LimiteDisponivel' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'Limite'],
            'ContaIdPagamento' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'DiaVencimento'],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->adicionarColunasSeNaoExistirem('tb_faturas', [
            'Status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'aberta', 'after' => 'ValorTotal'],
            'DataFechamento' => ['type' => 'DATE', 'null' => true, 'after' => 'AnoReferencia'],
            'DataAtualizacao' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    /**
     * Mantem o down sem remocao fisica para evitar perda acidental em producao.
     */
    public function down()
    {
        // Migration intencionalmente nao destrutiva.
    }

    /**
     * Adiciona somente colunas ausentes, permitindo executar com seguranca.
     */
    private function adicionarColunasSeNaoExistirem(string $tabela, array $colunas): void
    {
        if (! $this->db->tableExists($tabela)) {
            return;
        }

        $novasColunas = [];
        foreach ($colunas as $nome => $definicao) {
            if (! $this->db->fieldExists($nome, $tabela)) {
                $novasColunas[$nome] = $definicao;
            }
        }

        if ($novasColunas !== []) {
            $this->forge->addColumn($tabela, $novasColunas);
        }
    }
}
