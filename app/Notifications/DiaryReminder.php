<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiaryReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('今日の内省を記録しましょう')
            ->line('こんにちは、' . $notifiable->name . 'さん')
            ->line('今日も1日お疲れ様でした。')
            ->line('今日の内省を記録して、理想の自分に近づく一歩を踏み出しましょう。')
            ->action('内省を始める', route('diary.chat'))
            ->line('内省を習慣化することで、自分自身をより深く理解できるようになります。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => '今日の内省を記録しましょう',
            'url' => route('diary.chat'),
        ];
    }
}