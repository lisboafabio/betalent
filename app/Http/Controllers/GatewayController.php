<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeGatewayPriorityRequest;
use App\Models\Gateway;

class GatewayController extends Controller
{
    public function activate(string $id)
    {
        $gateway = Gateway::where('id', $id)->first();

        if (!$gateway) {
            return response()->json(['message' => 'Gateway not found'], 404);
        }

        $gateway->update(['is_active' => true]);

        return response()->json(['message' => 'Gateway activated', 'gateway' => $gateway]);
    }

    public function deactivate(string $id)
    {
        $gateway = Gateway::where('id', $id)->first();

        if (!$gateway) {
            return response()->json(['message' => 'Gateway not found'], 404);
        }

        $gateway->update(['is_active' => false]);

        return response()->json(['message' => 'Gateway deactivated', 'gateway' => $gateway]);
    }

    public function changePriority(ChangeGatewayPriorityRequest $request, string $id)
    {
        $gateway = Gateway::where('id', $id)->first();

        if (!$gateway) {
            return response()->json(['message' => 'Gateway not found'], 404);
        }

        $gateway->update(['priority' => $request->validated('priority')]);

        return response()->json(['message' => 'Gateway priority updated', 'gateway' => $gateway]);
    }
}
