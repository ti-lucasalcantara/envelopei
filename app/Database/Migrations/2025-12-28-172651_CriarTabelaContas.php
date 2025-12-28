<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaContas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ContaId' => [
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
            'TipoConta' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'banco', // banco, carteira, poupanca, investimento
            ],
            'SaldoInicial' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
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

        $this->forge->addKey('ContaId', true);
        $this->forge->addKey(['UsuarioId'], false, false, 'ix_contas_usuario');
        $this->forge->addKey(['UsuarioId', 'Ativa'], false, false, 'ix_contas_usuario_ativa');

        $this->forge->addForeignKey('UsuarioId', 'tb_usuarios', 'UsuarioId', 'CASCADE', 'RESTRICT', 'fk_contas_usuario');

        $this->forge->createTable('tb_contas', true, [
            'ENGINE'  => 'InnoDB',
            'DEFAULT CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tb_contas', true);
    }
}
