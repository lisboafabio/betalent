<?php

namespace App\Domain\Gateway\Adapters;

use App\Domain\Gateway\Contracts\PaymentGatewayInterface;
use App\Domain\Gateway\Dto\GatewayDto;
use Illuminate\Support\Facades\Http;
use Exception;

class GatewayTwoAdapter implements PaymentGatewayInterface
{
    private string $baseUrl = 'http://localhost:3002';
    private array $headers = [
        'Gateway-Auth-Token' => 'tk_f2198cc671b5289fa856',
        'Gateway-Auth-Secret' => '3d15e8ed6131446ea7e3456728b1211f'
    ];

    public function charge(GatewayDto $dto): array
    {
        $response = Http::withHeaders($this->headers)
            ->post("{$this->baseUrl}/transacoes", [
                'valor' => $dto->amount,
                'nome' => $dto->name,
                'email' => $dto->email,
                'numeroCartao' => $dto->card_number,
                'cvv' => $dto->cvv
            ]);

        if ($response->failed()) {
            throw new Exception("Gateway 2 charge failed: " . $response->body());
        }

        return $response->json();
    }

    public function refund(string $transactionId): array
    {
        $response = Http::withHeaders($this->headers)
            ->post("{$this->baseUrl}/transacoes/reembolso", [
                'id' => $transactionId
            ]);

        if ($response->failed()) {
            throw new Exception("Gateway 2 refund failed: " . $response->body());
        }

        return $response->json();
    }

    public function listTransactions(): array
    {
        $response = Http::withHeaders($this->headers)
            ->get("{$this->baseUrl}/transacoes");

        return $response->json();
    }
}
