<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterarItensEnvelopePagoIndividual extends Migration
{
    public function up()
    {
        $fields = [
            'Pago' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'FaturaId',
            ],
            'DataPagamento' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'Pago',
            ],
            'ContaIdPagamento' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'DataPagamento',
            ],
        ];

        $this->forge->addColumn('tb_itens_envelope', $fields);

        // Marca itens como pagos quando a fatura já está paga (compatibilidade)
        $db = db_connect();
        $db->query("
            UPDATE tb_itens_envelope ie
            INNER JOIN tb_faturas f ON f.FaturaId = ie.FaturaId
            SET ie.Pago = 1, ie.DataPagamento = f.DataPagamento, ie.ContaIdPagamento = f.ContaIdPagamento
            WHERE ie.FaturaId IS NOT NULL AND f.Pago = 1
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('tb_itens_envelope', ['Pago', 'DataPagamento', 'ContaIdPagamento']);
    }
}
