<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json(Client::paginate());
    }

    public function show(Client $client)
    {
        $client->load('transactions');
        return response()->json($client);
    }
}
