<?php

namespace App\Domain\Gateway\Contracts;

use App\Domain\Gateway\Dto\GatewayDto;

interface PaymentGatewayInterface
{
    /**
     * @return array
     */
    public function charge(GatewayDto $dto): array;

    public function refund(string $transactionId): array;

    public function listTransactions(): array;
}
