<?php

namespace App\Domain\Gateway\Dto;

use Spatie\LaravelData\Data;

class GatewayDto extends Data
{
    public int $amount;

    public string $name;

    public string $email;

    public string $card_number;

    public string $cvv;
}
