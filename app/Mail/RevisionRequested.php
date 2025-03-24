<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisionRequested extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * The revision comments.
     *
     * @var string
     */
    public $comments;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Order  $order
     * @param  string  $comments
     * @return void
     */
    public function __construct(Order $order, $comments)
    {
        $this->order = $order;
        $this->comments = $comments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Revision Requested for Order #' . $this->order->id)
                    ->view('emails.revision-requested');
    }
}