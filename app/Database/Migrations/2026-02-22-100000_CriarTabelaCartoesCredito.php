<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaCartoesCredito extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'CartaoCreditoId' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'UsuarioId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'Nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'Bandeira' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'Ultimos4Digitos' => [
                'type'       => 'VARCHAR',
                'constraint' => 4,
                'null'       => true,
            ],
            'DiaFechamento' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 10,
            ],
            'DiaVencimento' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 17,
            ],
            'Limite' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
            ],
            'Cor' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'Ativo' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('CartaoCreditoId', true);
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_cartoes_credito_usuario');

        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_cartoes_credito_usuario');

        $this->forge->createTable('tb_cartoes_credito', true, [
            'ENGINE'         => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE'        => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_cartoes_credito', true);
    }
}
