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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $supportEmail = Cache::get('setting_support_email', 'help@technicalwriters.co.ke');
        $subject = 'Dispute for Order #' . $this->order->id;
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $supportEmail = Cache::get('setting_support_email', 'help@technicalwriters.co.ke');
        
        return new Content(
            view: 'emails.order-disputed',
            with: [
                'order' => $this->order,
                'reason' => $this->reason,
                'emailBody' => $this->getEmailBody(),
                'supportEmail' => $supportEmail
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
        $supportEmail = Cache::get('setting_support_email', 'help@technicalwriters.co.ke');
        
        $body = Cache::get('email_template_account_suspension_body', 
            "Hello [Writer_Name],

We regret to inform you that there is a dispute on your order.

Order ID: [Order_ID]
Title: [Order_Title]
Dispute Reason: [Dispute_Reason]

Please log in to your dashboard to view the full details and address the issues raised.

If you need assistance, please contact our support team at [Support_Email].

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
        $supportEmail = Cache::get('setting_support_email', 'help@technicalwriters.co.ke');
        
        $replacements = [
            '[Writer_Name]' => $writerName,
            '[Order_ID]' => $this->order->id,
            '[Order_Title]' => $this->order->title,
            '[Dispute_Reason]' => $this->reason,
            '[Support_Email]' => $supportEmail,
            '[Writer_Dashboard_URL]' => $writerDashboardUrl,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}