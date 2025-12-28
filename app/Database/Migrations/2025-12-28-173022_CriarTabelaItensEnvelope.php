<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaItensEnvelope extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ItemEnvelopeId' => [
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

        $this->forge->addKey('ItemEnvelopeId', true);
        $this->forge->addKey(['LancamentoId'], false, false, 'ix_itens_envelope_lancamento');
        $this->forge->addKey(['EnvelopeId'], false, false, 'ix_itens_envelope_envelope');
        $this->forge->addKey(['EnvelopeId', 'LancamentoId'], false, false, 'ix_itens_envelope_envelope_lancamento');

        $this->forge->addForeignKey('LancamentoId', 'tb_lancamentos', 'LancamentoId', 'CASCADE', 'RESTRICT', 'fk_itens_envelope_lancamento');
        $this->forge->addForeignKey('EnvelopeId', 'tb_envelopes', 'EnvelopeId', 'RESTRICT', 'RESTRICT', 'fk_itens_envelope_envelope');

        $this->forge->createTable('tb_itens_envelope', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_itens_envelope', true);
    }
}
