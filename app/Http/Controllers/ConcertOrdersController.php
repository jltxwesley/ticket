<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;

class ConcertOrdersController extends Controller
{
    public function store(PaymentGateway $paymentGateway, $concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email'           => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token'   => 'required'
        ]);

        try {
            // find some tickets
            $tickets = $concert->findTickets(request('ticket_quantity'));

            // charge the customer for the tickets
            $paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

            // create an order for those tickets
            $order = Order::forTickets($tickets, request('email'));

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
