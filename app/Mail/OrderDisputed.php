<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDisputed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * The dispute reason.
     *
     * @var string
     */
    public $reason;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $reason
     * @return void
     */
    public function __construct(Order $order, $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Dispute for Order #' . $this->order->id)
                    ->view('emails.order-disputed');
    }
}