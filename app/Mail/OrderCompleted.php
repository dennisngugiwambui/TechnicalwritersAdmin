<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class OrderCompleted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = Cache::get('email_template_order_completion_subject', 'Order Completed: [Order_ID] - [Order_Title]');
        
        // Replace placeholders
        $subject = $this->replacePlaceholders($subject);
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-completed',
            with: [
                'order' => $this->order,
                'emailBody' => $this->getEmailBody(),
                'payment' => number_format($this->order->price * 0.7, 2),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
    
    /**
     * Get the email body from the cache or use the default
     *
     * @return string
     */
    protected function getEmailBody()
    {
        $body = Cache::get('email_template_order_completion_body', 
            "Hello [Writer_Name],

Congratulations! Your work on the following order has been completed and approved.

Order ID: [Order_ID]
Title: [Order_Title]
Payment Amount: $[Order_Price]

Your payment will be processed according to our payment schedule. You can view your payment details in your dashboard.

Thank you for your excellent work!

Best regards,
Technical Writers Team");
        
        // Replace placeholders
        return $this->replacePlaceholders($body);
    }
    
    /**
     * Replace placeholders in email content with actual values
     *
     * @param string $content
     * @return string
     */
    protected function replacePlaceholders($content)
    {
        $writerName = $this->order->writer ? $this->order->writer->name : 'Writer';
        $writerDashboardUrl = route('writer.orders.show', $this->order->id);
        $writerPayment = number_format($this->order->price * 0.7, 2); // 70% of the order price
        
        $replacements = [
            '[Writer_Name]' => $writerName,
            '[Order_ID]' => $this->order->id,
            '[Order_Title]' => $this->order->title,
            '[Order_Price]' => $writerPayment,
            '[Writer_Dashboard_URL]' => $writerDashboardUrl,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}