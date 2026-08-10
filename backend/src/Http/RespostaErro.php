<?php

declare(strict_types=1);

namespace App\Http;

final class RespostaErro
{
    public static function enviar(int $status, string|array $mensagens): void
    {
        http_response_code($status);
        echo json_encode(['erros' => is_array($mensagens) ? $mensagens : [$mensagens]]);
        exit;
    }
}
