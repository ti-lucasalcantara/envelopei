<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaCategorias extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'CategoriaId' => [
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
            'TipoCategoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'ambos', // receita, despesa, ambos
            ],
            'Ativa' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('CategoriaId', true);
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_categorias_usuario');
        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_categorias_usuario');

        $this->forge->createTable('tb_categorias', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_categorias', true);
    }
}
