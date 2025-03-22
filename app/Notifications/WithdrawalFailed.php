<?php

namespace App\Notifications;

use App\Models\Finance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalFailed extends Notification implements ShouldQueue
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
            ->subject('Withdrawal Request Failed')
            ->greeting('Hello ' . $notifiable->name)
            ->line('We regret to inform you that your withdrawal request has failed to process.')
            ->line('Amount: $' . number_format($this->transaction->amount, 2))
            ->line('Reason: ' . ($this->transaction->description ?? 'The payment processor was unable to complete the transaction.'))
            ->line('Your funds have been returned to your account balance and are available for withdrawal again.')
            ->line('Please ensure your payment details are correct and try again, or contact our support team for assistance.')
            ->action('View Your Balance', url('/writer/finances'));
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