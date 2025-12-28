<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'UsuarioId' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'Nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'Email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
            ],
            'SenhaHash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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

        $this->forge->addKey('UsuarioId', true);
        $this->forge->addUniqueKey('Email', 'uk_usuarios_email');

        $this->forge->createTable('tb_usuarios', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_usuarios', true);
    }
}
