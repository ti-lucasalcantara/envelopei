<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaProdutosInvestimento extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ProdutoInvestimentoId' => [
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
            'TipoProduto' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'comment'    => 'CDB, LCI, LCA, RendaVariavel, Tesouro, FundoImobiliario, Outros',
            ],
            'ValorAplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
            ],
            'ValorAtual' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'default'    => 0.00,
            ],
            'DataCriacao' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'DataAtualizacao' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('ProdutoInvestimentoId', true);
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_produtos_investimento_usuario');

        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_produtos_investimento_usuario');

        $this->forge->createTable('tb_produtos_investimento', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_produtos_investimento', true);
    }
}
