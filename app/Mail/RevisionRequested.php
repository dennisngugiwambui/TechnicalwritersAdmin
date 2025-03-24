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
use Carbon\Carbon;

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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = Cache::get('email_template_revision_request_subject', 'Revision Requested: [Order_ID] - [Order_Title]');
        
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
            view: 'emails.revision-requested',
            with: [
                'order' => $this->order,
                'comments' => $this->comments,
                'emailBody' => $this->getEmailBody(),
                'revisionDeadline' => Carbon::now()->addDays(1)->format('F j, Y, g:i a'),
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
        $body = Cache::get('email_template_revision_request_body', 
            "Hello [Writer_Name],

A revision has been requested for your order.

Order ID: [Order_ID]
Title: [Order_Title]
Revision Comments: [Revision_Comments]

Please log in to your dashboard to view the full details and submit the revised work by [Revision_Deadline].

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
        $revisionDeadline = Carbon::now()->addDays(1)->format('F j, Y, g:i a');
        
        $replacements = [
            '[Writer_Name]' => $writerName,
            '[Order_ID]' => $this->order->id,
            '[Order_Title]' => $this->order->title,
            '[Revision_Comments]' => $this->comments,
            '[Revision_Deadline]' => $revisionDeadline,
            '[Writer_Dashboard_URL]' => $writerDashboardUrl,
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}