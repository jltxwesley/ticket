<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250
        ]);

        $response = $this->json('POST', '/concerts/' . $concert->id . '/orders', [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@exmaple.com')->first();
        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', '/concerts/' . $concert->id . '/orders', [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email'); //$response->decodeResponseJson();
    }
}
