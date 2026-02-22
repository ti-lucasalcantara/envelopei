<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaFaturas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'FaturaId' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'CartaoCreditoId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'MesReferencia' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
            ],
            'AnoReferencia' => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
            ],
            'DataVencimento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'ValorTotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0,
            ],
            'Pago' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'DataPagamento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ContaIdPagamento' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('FaturaId', true);
        $this->forge->addKey(['CartaoCreditoId', 'MesReferencia', 'AnoReferencia'], false, false, 'ix_faturas_cartao_mes_ano');
        $this->forge->addUniqueKey(['CartaoCreditoId', 'MesReferencia', 'AnoReferencia'], 'uq_faturas_cartao_mes_ano');

        $this->forge->addForeignKey('CartaoCreditoId', 'tb_cartoes_credito', 'CartaoCreditoId', 'CASCADE', 'RESTRICT', 'fk_faturas_cartao');
        $this->forge->addForeignKey('ContaIdPagamento', 'tb_contas', 'ContaId', 'SET NULL', 'RESTRICT', 'fk_faturas_conta');

        $this->forge->createTable('tb_faturas', true, [
            'ENGINE'         => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE'        => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_faturas', true);
    }
}
