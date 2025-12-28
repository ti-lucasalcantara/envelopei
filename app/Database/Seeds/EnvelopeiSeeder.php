<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EnvelopeiSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();

        // evita duplicar seed
        $usuarioExistente = $db->table('tb_usuarios')
            ->where('Email', 'lucas@teste.com')
            ->get()
            ->getRowArray();

        if ($usuarioExistente) {
            echo "Usuário já existe: lucas@teste.com (UsuarioId {$usuarioExistente['UsuarioId']})\n";
            return;
        }

        $agora = date('Y-m-d H:i:s');

        // 1) Usuário
        $senhaHash = password_hash('2212', PASSWORD_DEFAULT);

        $db->table('tb_usuarios')->insert([
            'Nome'       => 'Lucas',
            'Email'      => 'lucas@teste.com',
            'SenhaHash'  => $senhaHash,
            'Ativo'      => 1,
            'DataCriacao'=> $agora,
        ]);

        $usuarioId = (int)$db->insertID();

        // 2) Contas (exemplos)
        $contas = [
            [
                'UsuarioId'    => $usuarioId,
                'Nome'         => 'Nubank',
                'TipoConta'    => 'banco',
                'SaldoInicial' => 0.00,
                'Ativa'        => 1,
                'DataCriacao'  => $agora,
            ],
            [
                'UsuarioId'    => $usuarioId,
                'Nome'         => 'Carteira',
                'TipoConta'    => 'carteira',
                'SaldoInicial' => 0.00,
                'Ativa'        => 1,
                'DataCriacao'  => $agora,
            ],
            [
                'UsuarioId'    => $usuarioId,
                'Nome'         => 'Reserva (Poupança)',
                'TipoConta'    => 'poupanca',
                'SaldoInicial' => 0.00,
                'Ativa'        => 1,
                'DataCriacao'  => $agora,
            ],
        ];
        $db->table('tb_contas')->insertBatch($contas);

        // 3) Envelopes (exemplos)
        $envelopes = [
            ['Nome' => 'Moradia',         'Cor' => '#0d6efd', 'Ordem' => 1],
            ['Nome' => 'Alimentação',     'Cor' => '#198754', 'Ordem' => 2],
            ['Nome' => 'Transporte',      'Cor' => '#fd7e14', 'Ordem' => 3],
            ['Nome' => 'Saúde',           'Cor' => '#dc3545', 'Ordem' => 4],
            ['Nome' => 'Lazer',           'Cor' => '#6f42c1', 'Ordem' => 5],
            ['Nome' => 'Educação',        'Cor' => '#20c997', 'Ordem' => 6],
            ['Nome' => 'Contas Fixas',    'Cor' => '#0dcaf0', 'Ordem' => 7],
            ['Nome' => 'Reserva/Meta',    'Cor' => '#6c757d', 'Ordem' => 8],
            ['Nome' => 'Imprevistos',     'Cor' => '#343a40', 'Ordem' => 9],
        ];

        $payloadEnvelopes = [];
        foreach ($envelopes as $e) {
            $payloadEnvelopes[] = [
                'UsuarioId'   => $usuarioId,
                'Nome'        => $e['Nome'],
                'Cor'         => $e['Cor'],
                'Ordem'       => $e['Ordem'],
                'Ativo'       => 1,
                'DataCriacao' => $agora,
            ];
        }

        $db->table('tb_envelopes')->insertBatch($payloadEnvelopes);

        echo "Seed concluído! UsuarioId: {$usuarioId} | Email: lucas@teste.com | Senha: 2212\n";
    }
}
