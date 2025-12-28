<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaLancamentos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'LancamentoId' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'UsuarioId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'CategoriaId' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'TipoLancamento' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                // receita, despesa, transferencia, ajuste
            ],
            'Descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'DataLancamento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('LancamentoId', true);
        $this->forge->addKey(['UsuarioId', 'DataLancamento'], false, false, 'ix_lancamentos_usuario_data');
        $this->forge->addKey(['TipoLancamento', 'DataLancamento'], false, false, 'ix_lancamentos_tipo_data');

        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_lancamentos_usuario');
        $this->forge->addForeignKey('CategoriaId', 'tb_categorias', 'CategoriaId', 'SET NULL', 'RESTRICT', 'fk_lancamentos_categoria');

        $this->forge->createTable('tb_lancamentos', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_lancamentos', true);
    }
}
