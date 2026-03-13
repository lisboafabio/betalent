<?php

namespace App\Domain\Gateway\Services;

use App\Domain\Gateway\Contracts\PaymentGatewayInterface;
use App\Models\Gateway;
use Exception;

class PaymentGatewayManager
{
    /**
     * Resolves the highest priority active gateway model and its adapter.
     */
    public function resolve(): array
    {
        // Get the gateway with highest priority that is active
        $gatewayModel = Gateway::where('is_active', true)
            ->orderBy('priority', 'asc') // Lower number = higher priority
            ->first();

        if (!$gatewayModel) {
            throw new Exception("No active gateways available.");
        }

        return [
            'model' => $gatewayModel,
            'adapter' => $this->getAdapter($gatewayModel->name)
        ];
    }

    public function resolveById(int $gatewayId): PaymentGatewayInterface
    {
        $gatewayModel = Gateway::findOrFail($gatewayId);
        return $this->getAdapter($gatewayModel->name);
    }

    private function getAdapter(string $name): PaymentGatewayInterface
    {
        return match (strtolower($name)) {
            'gateway 1' => app(\App\Domain\Gateway\Adapters\GatewayOneAdapter::class),
            'gateway 2' => app(\App\Domain\Gateway\Adapters\GatewayTwoAdapter::class),
            default => throw new Exception("Unsupported gateway: {$name}"),
        };
    }
}
