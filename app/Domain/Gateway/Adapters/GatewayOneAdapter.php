<?php

namespace App\Domain\Gateway\Adapters;

use App\Domain\Gateway\Contracts\PaymentGatewayInterface;
use App\Domain\Gateway\Dto\GatewayDto;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GatewayOneAdapter implements PaymentGatewayInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.gateways.gateway_one');
    }

    public function charge(GatewayDto $dto): array
    {
        $response = Http::withToken($this->getToken())
            ->post("{$this->baseUrl}/transactions", [
                'amount' => $dto->amount,
                'name' => $dto->name,
                'email' => $dto->email,
                'cardNumber' => $dto->card_number,
                'cvv' => $dto->cvv,
            ]);

        if ($response->failed()) {
            throw new Exception('Gateway 1 charge failed: '.$response->body());
        }

        return $response->json();
    }

    public function refund(string $transactionId): array
    {
        $response = Http::withToken($this->getToken())
            ->post("{$this->baseUrl}/transactions/{$transactionId}/charge_back");

        if ($response->failed()) {
            throw new Exception('Gateway 1 refund failed: '.$response->body());
        }

        return $response->json();
    }

    public function listTransactions(): array
    {
        $response = Http::withToken($this->getToken())
            ->get("{$this->baseUrl}/transactions");

        return $response->json();
    }

    private function getToken(): string
    {
        return Cache::remember('gateway_one_token', 3600, function () {
            $response = Http::post("{$this->baseUrl}/login", [
                'email' => 'dev@betalent.tech',
                'token' => 'FEC9BB078BF338F464F96B48089EB498',
            ]);

            if ($response->failed()) {
                throw new Exception('Gateway 1 Login failed');
            }

            return $response->json('token');
        });
    }
}
