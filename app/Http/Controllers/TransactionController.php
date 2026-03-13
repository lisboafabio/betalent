<?php

namespace App\Http\Controllers;

use App\Domain\Gateway\Dto\GatewayDto;
use App\Domain\Gateway\Services\PaymentGatewayManager;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Client;
use App\Models\Product;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request, PaymentGatewayManager $manager)
    {
        $validated = $request->validated();
        $product = Product::where('id', $validated['product_id'])->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $totalAmount = $product->amount * $validated['quantity'];

        $clientData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        $paymentData = [
            'amount' => $totalAmount,
            'card_number' => $validated['cardNumber'],
            'cvv' => $validated['cvv'],
        ];

        $resolved = $manager->resolve();
        $activeGatewayModel = $resolved['model'];
        $gatewayAdapter = $resolved['adapter'];

        $client = Client::firstOrCreate(['email' => $clientData['email']], ['name' => $clientData['name']]);

        $gatewayData = GatewayDto::from(array_merge($paymentData, $clientData));

        try {
            $gatewayResponse = $gatewayAdapter->charge($gatewayData);

            $transaction = Transaction::create([
                'client_id' => $client->id,
                'gateway_id' => $activeGatewayModel->id,
                'external_id' => $gatewayResponse['id'] ?? null,
                'status' => 'paid',
                'amount' => $totalAmount,
                'card_last_numbers' => substr($validated['cardNumber'], -4),
            ]);

            $transaction->products()->attach($product->id, ['quantity' => $validated['quantity']]);

            return response()->json($transaction->load(['client', 'gateway', 'products']), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment failed', 'error' => $e->getMessage()], 422);
        }
    }

    public function index()
    {
        return response()->json(Transaction::with(['client', 'gateway'])->paginate());
    }

    public function show(string $id)
    {
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction?->load(['client', 'gateway', 'products']));
    }

    public function refund(string $id, PaymentGatewayManager $manager)
    {
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status === 'refunded') {
            return response()->json(['message' => 'Already refunded'], 422);
        }

        try {
            $adapter = $manager->resolveById($transaction->gateway_id);
            $adapter->refund($transaction->external_id);

            $transaction->update(['status' => 'refunded']);

            return response()->json(['message' => 'Refund successful', 'transaction' => $transaction]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Refund failed', 'error' => $e->getMessage()], 422);
        }
    }
}
