<?php

namespace App\Models\Envelopei;

use CodeIgniter\Model;

class BaseEnvelopeiModel extends Model
{
    protected $returnType = 'array';

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setDataCriacao'];

    protected function setDataCriacao(array $data)
    {
        if (!isset($data['data']['DataCriacao']) || empty($data['data']['DataCriacao'])) {
            $data['data']['DataCriacao'] = date('Y-m-d H:i:s');
        }

        return $data;
    }
}
