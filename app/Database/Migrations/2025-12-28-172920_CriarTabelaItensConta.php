<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaItensConta extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ItemContaId' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'LancamentoId' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'ContaId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'Valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('ItemContaId', true);
        $this->forge->addKey(['LancamentoId'], false, false, 'ix_itens_conta_lancamento');
        $this->forge->addKey(['ContaId'], false, false, 'ix_itens_conta_conta');
        $this->forge->addKey(['ContaId', 'LancamentoId'], false, false, 'ix_itens_conta_conta_lancamento');

        $this->forge->addForeignKey('LancamentoId', 'tb_lancamentos', 'LancamentoId', 'CASCADE', 'RESTRICT', 'fk_itens_conta_lancamento');
        $this->forge->addForeignKey('ContaId', 'tb_contas', 'ContaId', 'RESTRICT', 'RESTRICT', 'fk_itens_conta_conta');

        $this->forge->createTable('tb_itens_conta', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_itens_conta', true);
    }
}
