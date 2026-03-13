<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_public_can_create_transaction()
    {
        $gateway = Gateway::factory()->create(['name' => 'Gateway 1', 'priority' => 1]); // Priority matters for resolving
        $product = Product::factory()->create(['amount' => 1000]);

        Http::fake([
            'http://localhost:3001/login' => Http::response(['token' => 'mocked'], 200),
            'http://localhost:3001/transactions' => Http::response(['id' => 'gate-123', 'status' => 'approved'], 200),
        ]);

        $response = $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'quantity' => 2,
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'cardNumber' => '5569000000006063',
            'cvv' => '010',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('amount', 2000)
            ->assertJsonPath('status', 'paid');

        $this->assertDatabaseHas('transactions', [
            'amount' => 2000,
            'status' => 'paid',
            'external_id' => 'gate-123',
            'gateway_id' => $gateway->id,
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'email' => 'john@test.com',
        ]);
    }

    public function test_admin_can_refund_transaction()
    {
        $gateway = Gateway::factory()->create(['name' => 'Gateway 2', 'priority' => 1]);

        $transaction = Transaction::factory()->create([
            'gateway_id' => $gateway->id,
            'external_id' => 'trans-456',
        ]);

        Http::fake([
            'http://localhost:3002/transacoes/reembolso' => Http::response(['status' => 'refunded'], 200),
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/transactions/{$transaction->id}/refund");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Refund successful');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'refunded',
        ]);
    }

    public function test_validation_fails_for_invalid_transaction()
    {
        $response = $this->postJson('/api/transactions', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'quantity', 'name', 'email', 'cardNumber', 'cvv']);
    }

    public function test_admin_can_list_transactions_paginated()
    {
        Transaction::factory()->count(2)->create();

        $response = $this->actingAs($this->admin, 'api')->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
    public function test_cannot_view_non_existent_transaction()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/transactions/999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Transaction not found');
    }

    public function test_cannot_refund_non_existent_transaction()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/transactions/999/refund');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Transaction not found');
    }
}
