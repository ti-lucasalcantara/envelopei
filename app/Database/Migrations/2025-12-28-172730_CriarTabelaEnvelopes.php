<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaEnvelopes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'EnvelopeId' => [
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
                'constraint' => 120,
            ],
            'Cor' => [
                'type'       => 'VARCHAR',
                'constraint' => 12,
                'null'       => true,
            ],
            'Ordem' => [
                'type'     => 'INT',
                'null'     => true,
            ],
            'Ativo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('EnvelopeId', true);
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_envelopes_usuario');
        $this->forge->addKey(['UsuarioId', 'Ativo'], false, false, 'ix_envelopes_usuario_ativo');

        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_envelopes_usuario');

        $this->forge->createTable('tb_envelopes', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_envelopes', true);
    }
}
