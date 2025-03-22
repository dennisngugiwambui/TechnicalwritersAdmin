<?php

namespace App\Notifications;

use App\Models\Finance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BonusReceived extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Finance $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bonus Received: $' . number_format($this->transaction->amount, 2))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! A bonus has been added to your account.')
            ->line('Amount: $' . number_format($this->transaction->amount, 2))
            ->line('Reason: ' . $this->transaction->description)
            ->line('This amount has been added to your available balance.')
            ->action('View Your Balance', url('/writer/finances'))
            ->line('Thank you for your excellent work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'description' => $this->transaction->description,
            'created_at' => $this->transaction->created_at,
        ];
    }
}