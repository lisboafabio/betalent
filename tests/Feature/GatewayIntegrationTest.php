<?php

namespace Tests\Feature;

use App\Domain\Gateway\Adapters\GatewayOneAdapter;
use App\Domain\Gateway\Adapters\GatewayTwoAdapter;
use App\Domain\Gateway\Dto\GatewayDto;
use App\Domain\Gateway\Services\PaymentGatewayManager;
use App\Models\Gateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_manager_resolves_highest_priority_active_gateway()
    {
        $gateway1 = Gateway::factory(['name' => 'Gateway 1', 'is_active' => true, 'priority' => 5])->create();
        Gateway::factory(['name' => 'Gateway 2', 'is_active' => true, 'priority' => 10])->create();
        Gateway::factory(['name' => 'Gateway 3', 'is_active' => false, 'priority' => 1])->create();

        $manager = app(PaymentGatewayManager::class);
        $resolved = $manager->resolve();

        $this->assertEquals($gateway1->id, $resolved['model']->id);
        $this->assertInstanceOf(GatewayOneAdapter::class, $resolved['adapter']);
    }

    public function test_gateway_one_adapter_charges_correctly()
    {
        Http::fake([
            'http://localhost:3001/login' => Http::response(['token' => 'mocked_token']),
            'http://localhost:3001/transactions' => Http::response(['id' => 'abc-123', 'status' => 'approved']),
        ]);

        $gatewayData = [
            'amount' => 1000,
            'name' => 'Tester',
            'email' => 'test@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
        ];

        $adapter = app(GatewayOneAdapter::class);
        $response = $adapter->charge(GatewayDto::from($gatewayData));

        $this->assertEquals('abc-123', $response['id']);
        $this->assertEquals('approved', $response['status']);
    }

    public function test_gateway_two_adapter_refunds_correctly()
    {
        Http::fake([
            'http://localhost:3002/transacoes/reembolso' => Http::response(['id' => '123', 'status' => 'refunded']),
        ]);

        $adapter = app(GatewayTwoAdapter::class);
        $response = $adapter->refund('123');

        $this->assertEquals('refunded', $response['status']);
    }

    public function test_gateway_two_adapter_charges_correctly()
    {
        Http::fake([
            'http://localhost:3002/transacoes' => Http::response(['id' => 'def-456', 'status' => 'approved']),
        ]);

        $gatewayData = [
            'amount' => 1000,
            'name' => 'Tester',
            'email' => 'test@test.com',
            'card_number' => '5569000000006063',
            'cvv' => '010',
        ];

        $adapter = app(GatewayTwoAdapter::class);
        $response = $adapter->charge(GatewayDto::from($gatewayData));

        $this->assertEquals('def-456', $response['id']);
        $this->assertEquals('approved', $response['status']);
    }

    public function test_gateway_one_adapter_refunds_correctly()
    {
        Http::fake([
            'http://localhost:3001/login' => Http::response(['token' => 'mocked_token']),
            'http://localhost:3001/transactions/123/charge_back' => Http::response(['id' => '123', 'status' => 'refunded']),
        ]);

        $adapter = app(GatewayOneAdapter::class);
        $response = $adapter->refund('123');

        $this->assertEquals('refunded', $response['status']);
    }

    public function test_gateway_one_adapter_list_transactions_correctly()
    {
        Http::fake([
            'http://localhost:3001/login' => Http::response(['token' => 'mocked_token']),
            'http://localhost:3001/transactions' => Http::response([['id' => '123', 'status' => 'approved']]),
        ]);

        $adapter = app(GatewayOneAdapter::class);
        $response = $adapter->listTransactions();

        $this->assertCount(1, $response);
        $this->assertEquals('123', $response[0]['id']);
    }

    public function test_gateway_two_adapter_list_transactions_correctly()
    {
        Http::fake([
            'http://localhost:3002/transacoes' => Http::response([['id' => '123', 'status' => 'approved']]),
        ]);

        $adapter = app(GatewayTwoAdapter::class);
        $response = $adapter->listTransactions();

        $this->assertCount(1, $response);
        $this->assertEquals('123', $response[0]['id']);
    }
}
