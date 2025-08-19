<?php

namespace App\Support;

class Turnstile
{
    public function validate(string $response): array
    {
        if (! empty(config('services.turnstile.secret_key'))) {
            $url = config('services.turnstile.url');
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POSTFIELDS => json_encode([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $response,
                ]),
            ]);

            $result = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($json = json_decode($result)) {
                if ($json->success) {
                    return [
                        'status' => 1,
                    ];
                }

                return [
                    'status' => 0,
                    'turnstile_response' => $json,
                ];
            }

            return [
                'status' => 0,
                'error' => 'Unknown error occurred',
            ];
        }

        return [
            'status' => 0,
            'error' => 'Turnstile secret not found',
        ];
    }
}
