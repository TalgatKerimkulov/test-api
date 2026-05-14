<?php

declare(strict_types=1);

namespace App\Services\Victoria;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VictoriaService
{
    public function send(array $payload): ?Response
    {
        $baseUrl = rtrim((string) config('services.victoria.base_uri', 'http://victoria-logs:9428'), '/');
        $login = (string) config('services.victoria.login', '');
        $password = (string) config('services.victoria.password', '');

        $jsonLine = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (! is_string($jsonLine) || $jsonLine === '') {
            return null;
        }

        $request = Http::withHeaders([
            'Content-Type' => 'application/stream+json',
            'AccountID' => '0',
            'ProjectID' => '0',
        ]);

        if ($login !== '' && $password !== '') {
            $request = $request->withBasicAuth($login, $password);
        }

        return $request
            ->timeout(10)
            ->withBody($jsonLine, 'application/stream+json')
            ->post("{$baseUrl}/insert/jsonline?_stream_fields=level,channel&_time_field=time&_msg_field=message");
    }
}

