<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaRateiosModeloItens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'RateioModeloItemId' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'RateioModeloId' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'EnvelopeId' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'ModoRateio' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'percentual | valor',
            ],

            'Valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'comment'    => 'Percentual (0-100) ou valor fixo',
            ],

            'Ordem' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('RateioModeloItemId', true);
        $this->forge->addKey('RateioModeloId');
        $this->forge->addKey('EnvelopeId');

        $this->forge->addForeignKey(
            'RateioModeloId',
            'tb_rateios_modelo',
            'RateioModeloId',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'EnvelopeId',
            'tb_envelopes',
            'EnvelopeId',
            'RESTRICT',
            'CASCADE'
        );

        $this->forge->createTable('tb_rateios_modelo_itens');
    }

    public function down()
    {
        $this->forge->dropTable('tb_rateios_modelo_itens');
    }
}
