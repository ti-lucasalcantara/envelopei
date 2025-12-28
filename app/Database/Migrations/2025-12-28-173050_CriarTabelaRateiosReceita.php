<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaRateiosReceita extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'RateioReceitaId' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'LancamentoId' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'EnvelopeId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'ModoRateio' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                // valor | percentual
            ],
            'ValorInformado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'ValorCalculado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('RateioReceitaId', true);
        $this->forge->addKey(['LancamentoId'], false, false, 'ix_rateios_receita_lancamento');
        $this->forge->addKey(['EnvelopeId'], false, false, 'ix_rateios_receita_envelope');
        $this->forge->addKey(['LancamentoId', 'EnvelopeId'], false, false, 'ix_rateios_receita_lancamento_envelope');

        $this->forge->addForeignKey('LancamentoId', 'tb_lancamentos', 'LancamentoId', 'CASCADE', 'RESTRICT', 'fk_rateios_receita_lancamento');
        $this->forge->addForeignKey('EnvelopeId', 'tb_envelopes', 'EnvelopeId', 'RESTRICT', 'RESTRICT', 'fk_rateios_receita_envelope');

        $this->forge->createTable('tb_rateios_receita', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_rateios_receita', true);
    }
}
