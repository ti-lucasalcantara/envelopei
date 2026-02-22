<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterarItensEnvelopeParaFatura extends Migration
{
    public function up()
    {
        $fields = [
            'FaturaId' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'EnvelopeId',
            ],
        ];

        $this->forge->addColumn('tb_itens_envelope', $fields);
        $this->forge->addForeignKey('FaturaId', 'tb_faturas', 'FaturaId', 'SET NULL', 'RESTRICT', 'fk_itens_envelope_fatura');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tb_itens_envelope', 'fk_itens_envelope_fatura');
        $this->forge->dropColumn('tb_itens_envelope', 'FaturaId');
    }
}
