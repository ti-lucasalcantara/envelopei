<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterarLancamentosParaCartao extends Migration
{
    public function up()
    {
        $fields = [
            'CartaoCreditoId' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'CategoriaId',
            ],
            'FaturaId' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'CartaoCreditoId',
            ],
        ];

        $this->forge->addColumn('tb_lancamentos', $fields);
        $this->forge->addForeignKey('CartaoCreditoId', 'tb_cartoes_credito', 'CartaoCreditoId', 'SET NULL', 'RESTRICT', 'fk_lancamentos_cartao');
        $this->forge->addForeignKey('FaturaId', 'tb_faturas', 'FaturaId', 'SET NULL', 'RESTRICT', 'fk_lancamentos_fatura');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tb_lancamentos', 'fk_lancamentos_cartao');
        $this->forge->dropForeignKey('tb_lancamentos', 'fk_lancamentos_fatura');
        $this->forge->dropColumn('tb_lancamentos', ['CartaoCreditoId', 'FaturaId']);
    }
}
