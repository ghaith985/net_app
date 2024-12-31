<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\File;


class FileReleased extends Notification
{
    use Queueable;

    protected $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // يمكن إرسال الإشعار عبر البريد الإلكتروني أو قاعدة البيانات
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->line('The file "' . $this->file->name . '" has been released.')
            ->action('View File', url('/files/' . $this->file->id))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'file_id' => $this->file->id,
            'message' => 'The file "' . $this->file->name . '" has been released.',
        ];
    }
}
