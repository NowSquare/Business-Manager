<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectTaskUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($actionURL, $sender, $recepient, $task)
    {
        $this->actionURL = $actionURL;
        $this->sender = $sender;
        $this->recepient = $recepient;
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (env('DEMO', false)) {
          return ['database'];
        } else {
          return ['mail', 'database'];
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
          ->from(config('system.mail_address_from'), config('system.mail_name_from'))
          ->subject(trans('g.task_updated_subject', ['subject' => $this->task->subject]))
          ->markdown('emails.action', [
            'actionURL' => $this->actionURL,
            'actionText' => trans('g.visit_dashboard'),
            'header' => config('system.name'),
            'salutation' => trans('g.email_salutation', ['name' => $this->recepient->name]),
            'body_top' => trans('g.task_updated_mail', ['sender' => $this->sender->name . ' (' . $this->sender->email . ')']),
            'body_footer' => '',
            'subcopy' => trans('g.email_trouble_clicking_button', ['actionText' => trans('g.visit_dashboard')]) . ' ' . $this->actionURL,
            'footer' => trans('g.email_ip_address') . ' **' . request()->ip() . '**'
          ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
          'type' => 'project-task',
          'title' => trans('g.task_updated_notification', ['sender' => $this->sender->name, 'subject' => $this->task->subject]),
          'created_by' => $this->sender->id,
          'project_id' => $this->task->project_id,
          'task_id' => $this->task->id,
        ];
    }
}
