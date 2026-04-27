<?php

if (! function_exists('usuario_logado_id')) {
    /**
     * Retorna o identificador do usuario autenticado na sessao atual.
     */
    function usuario_logado_id(): int
    {
        return (int) (session('UsuarioId') ?? 0);
    }
}

if (! function_exists('moeda_br')) {
    /**
     * Formata um valor numerico no padrao monetario brasileiro.
     */
    function moeda_br($valor): string
    {
        return 'R$ ' . number_format((float) ($valor ?? 0), 2, ',', '.');
    }
}

if (! function_exists('data_br')) {
    /**
     * Formata uma data ISO para o padrao brasileiro.
     */
    function data_br(?string $data): string
    {
        if (empty($data)) {
            return '-';
        }

        $timestamp = strtotime($data);
        return $timestamp ? date('d/m/Y', $timestamp) : '-';
    }
}

if (! function_exists('decimal_banco')) {
    /**
     * Converte valores digitados em pt-BR para decimal aceito pelo banco.
     */
    function decimal_banco($valor): float
    {
        if (is_numeric($valor)) {
            return round((float) $valor, 2);
        }

        $normalizado = str_replace(['R$', ' ', '.'], '', (string) $valor);
        $normalizado = str_replace(',', '.', $normalizado);

        return round((float) $normalizado, 2);
    }
}

if (! function_exists('classe_menu_ativo')) {
    /**
     * Marca o item do menu lateral quando a rota atual pertence ao modulo.
     */
    function classe_menu_ativo(string $prefixo): string
    {
        $uri = service('uri')->getPath();
        return str_starts_with($uri, trim($prefixo, '/')) ? 'active' : '';
    }
}
