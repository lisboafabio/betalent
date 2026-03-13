<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json(Client::paginate());
    }

    public function show(string $id)
    {
        $client = Client::where('id', $id)->first();

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $client?->load('transactions');

        return response()->json($client);
    }
}
