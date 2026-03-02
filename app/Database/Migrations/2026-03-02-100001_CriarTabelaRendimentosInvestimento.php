<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaRendimentosInvestimento extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'RendimentoId' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ProdutoInvestimentoId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'UsuarioId' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'Valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'null'       => false,
                'comment'    => 'Positivo = lucro, negativo = perda',
            ],
            'DataRendimento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'Descricao' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('RendimentoId', true);
        $this->forge->addKey(['ProdutoInvestimentoId'], false, false, 'ix_rendimentos_produto');
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_rendimentos_usuario');

        $this->forge->addForeignKey('ProdutoInvestimentoId', 'tb_produtos_investimento', 'ProdutoInvestimentoId', 'CASCADE', 'RESTRICT', 'fk_rendimentos_produto');
        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_rendimentos_usuario');

        $this->forge->createTable('tb_rendimentos_investimento', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_rendimentos_investimento', true);
    }
}
