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

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', '/concerts/' . $concert->id . '/orders', $params);
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($field); //$response->decodeResponseJson();
    }

    /** @test */
    public function customer_can_purchase_tickets_to_a_published_concert()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'email'           => 'john@exmaple.com',
                'ticket_quantity' => 3,
                'amount'          => 9750
            ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $this->assertTrue($concert->hasOrderFor('john@exmaple.com'));
        $this->assertEquals(3, $concert->ordersFor('john@exmaple.com')->first()->ticketsQuantity());
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->state('unpublished')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@exmaple.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails()
    {
        // $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token'
        ]);

        $response->assertStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@exmaple.com'));
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain()
    {
        // $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@exmaple.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@exmaple.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'         => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token'   => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3
        ]);

        $this->assertValidationError($response, 'payment_token');
    }
}
