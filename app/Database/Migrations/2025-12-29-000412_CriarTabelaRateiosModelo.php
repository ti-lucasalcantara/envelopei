<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaRateiosModelo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'RateioModeloId' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'UsuarioId' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'Nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'Padrao' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = modelo padrão do usuário',
            ],

            'Ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],

            'DataCriacao' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('RateioModeloId', true);
        $this->forge->addKey('UsuarioId');

        $this->forge->addForeignKey(
            'UsuarioId',
            'tb_usuarios',
            'UsuarioId',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('tb_rateios_modelo');
    }

    public function down()
    {
        $this->forge->dropTable('tb_rateios_modelo');
    }
}
