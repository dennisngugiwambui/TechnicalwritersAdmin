<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Finance;

class WithdrawalProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The withdrawal transaction instance.
     *
     * @var \App\Models\Finance
     */
    protected $transaction;
    
    /**
     * Whether the withdrawal was rejected.
     *
     * @var bool
     */
    protected $isRejected;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Finance $transaction
     * @param bool $isRejected
     * @return void
     */
    public function __construct(Finance $transaction, bool $isRejected = false)
    {
        $this->transaction = $transaction;
        $this->isRejected = $isRejected;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

     
    public function toMail($notifiable)
    {
        if ($this->isRejected) {
            return (new MailMessage)
                ->subject('Withdrawal Request Rejected')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your withdrawal request for $' . number_format($this->transaction->amount, 2) . ' has been rejected.')
                ->line('Reason: ' . substr(strstr($this->transaction->description, '| Rejected:'), 11))
                ->line('Your funds have been returned to your account balance.')
                ->line('If you have any questions, please contact support.')
                ->action('View Transaction', url('/writer/finance/transaction/' . $this->transaction->id));
        }
        
        return (new MailMessage)
            ->subject('Withdrawal Request Processed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your withdrawal request for $' . number_format($this->transaction->amount, 2) . ' has been processed successfully.')
            ->line('Transaction ID: ' . $this->transaction->payment_reference)
            ->line('The funds should be available in your account shortly.')
            ->action('View Transaction', url('/writer/finance/transaction/' . $this->transaction->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($this->isRejected) {
            return [
                'title' => 'Withdrawal Request Rejected',
                'message' => 'Your withdrawal request for $' . number_format($this->transaction->amount, 2) . ' has been rejected.',
                'transaction_id' => $this->transaction->id,
            ];
        }
        
        return [
            'title' => 'Withdrawal Request Processed',
            'message' => 'Your withdrawal request for $' . number_format($this->transaction->amount, 2) . ' has been processed.',
            'transaction_id' => $this->transaction->id,
        ];
    }
}