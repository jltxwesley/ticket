<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function forTickets($tickets, $email)
    {
        $order = self::create([
            'email'  => $email,
            'amount' => $tickets->sum('price')
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketsQuantity()
    {
        return $this->tickets()->count();
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    public function toArray()
    {
        return  [
            'email'           => $this->email,
            'ticket_quantity' => $this->ticketsQuantity(),
            'amount'          => $this->amount
       ];
    }
}
