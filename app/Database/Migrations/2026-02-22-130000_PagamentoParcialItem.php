<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PagamentoParcialItem extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_itens_envelope', [
            'ValorPago' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0,
                'after'      => 'Valor',
            ],
        ]);

        $db = db_connect();
        $db->query("
            UPDATE tb_itens_envelope ie
            INNER JOIN tb_faturas f ON f.FaturaId = ie.FaturaId
            SET ie.ValorPago = ABS(ie.Valor)
            WHERE ie.FaturaId IS NOT NULL AND (ie.Pago = 1 OR f.Pago = 1)
        ");

        $this->forge->addField([
            'PagamentoItemId' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ItemEnvelopeId' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'Valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'Descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'DataPagamento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'ContaIdPagamento' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'LancamentoId' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        $this->forge->addKey('PagamentoItemId', true);
        $this->forge->addKey(['ItemEnvelopeId'], false, false, 'ix_pagamentos_item_item');
        $this->forge->addForeignKey('ItemEnvelopeId', 'tb_itens_envelope', 'ItemEnvelopeId', 'CASCADE', 'RESTRICT', 'fk_pagamentos_item_ie');
        $this->forge->addForeignKey('ContaIdPagamento', 'tb_contas', 'ContaId', 'RESTRICT', 'RESTRICT', 'fk_pagamentos_item_conta');
        $this->forge->addForeignKey('LancamentoId', 'tb_lancamentos', 'LancamentoId', 'CASCADE', 'RESTRICT', 'fk_pagamentos_item_lancamento');

        $this->forge->createTable('tb_pagamentos_item', true, [
            'ENGINE'         => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE'        => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_pagamentos_item', true);
        $this->forge->dropColumn('tb_itens_envelope', 'ValorPago');
    }
}
