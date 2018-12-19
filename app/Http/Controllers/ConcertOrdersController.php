<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller
{
    public function store(PaymentGateway $paymentGateway, Concert $concert)
    {
        $this->validate(request(), [
            'email' => 'required'
        ]);

        $paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

        $order = $concert->orderTickets(request('email'), request('ticket_quantity'));

        return response()->json([], 201);
    }
}
