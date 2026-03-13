<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Http\Requests\ChangeGatewayPriorityRequest;

class GatewayController extends Controller
{
    public function activate(Gateway $gateway)
    {
        $gateway->update(['is_active' => true]);
        return response()->json(['message' => 'Gateway activated', 'gateway' => $gateway]);
    }

    public function deactivate(Gateway $gateway)
    {
        $gateway->update(['is_active' => false]);
        return response()->json(['message' => 'Gateway deactivated', 'gateway' => $gateway]);
    }

    public function changePriority(ChangeGatewayPriorityRequest $request, Gateway $gateway)
    {
        $gateway->update(['priority' => $request->validated('priority')]);
        return response()->json(['message' => 'Gateway priority updated', 'gateway' => $gateway]);
    }
}
