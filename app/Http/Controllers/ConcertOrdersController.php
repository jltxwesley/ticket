<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller
{
    public function store(PaymentGateway $paymentGateway, Concert $concert)
    {
        $ticketQuantity = request('ticket_quantity');
        $amount = $ticketQuantity * $concert->ticket_price;

        $paymentGateway->charge($amount, request('payment_token'));

        $order = $concert->orders()->create(['email' => request('email')]);

        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return response()->json([], 201);
    }
}
