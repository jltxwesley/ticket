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

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        $this->withoutExceptionHandling();

        $paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $paymentGateway);

        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250
        ]);

        $response = $this->json('POST', '/concerts/' . $concert->id . '/orders', [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(201);

        $this->assertEquals(9750, $paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@exmaple.com')->first();
        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets()->count());
    }
}
