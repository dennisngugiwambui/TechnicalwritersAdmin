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

class OrderAssigned extends Mailable
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
        $subject = Cache::get('email_template_order_assignment_subject', 'New Order Assignment: #[Order_ID] - [Order_Title]');
        
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
            view: 'emails.order-assigned',
            with: [
                'order' => $this->order,
                'emailBody' => $this->getEmailBody(),
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
        $body = Cache::get('email_template_order_assignment_body', 
            "Hello [Writer_Name],

You have been assigned a new order.

Order ID: [Order_ID]
Title: [Order_Title]
Deadline: [Order_Deadline]
Payment: $[Order_Price]

Please log in to your dashboard to view the full details and accept the assignment.

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
        $writerDashboardUrl = route('home');
        $orderDeadline = $this->order->deadline ? $this->order->deadline->format('F j, Y, g:i a') : 'Not specified';
        
        $replacements = [
            '[Writer_Name]' => $writerName,
            '[Order_ID]' => $this->order->id,
            '[Order_Title]' => $this->order->title,
            '[Order_Deadline]' => $orderDeadline,
            '[Order_Price]' => number_format($this->order->price, 2),
            '[Writer_Dashboard_URL]' => $writerDashboardUrl,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}