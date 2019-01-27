<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($actionURL, $name)
    {
        $this->actionURL = $actionURL;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.action')
          ->from(config('system.mail_address_from'), config('system.mail_name_from'))
          ->subject(trans('g.reset_password'))
          ->with([
              'actionURL' => $this->actionURL,
              'actionText' => trans('g.reset_password'),
              'header' => config('system.name'),
              'salutation' => trans('g.email_salutation', ['name' => $this->name]),
              'body_top' => trans('g.reset_password_mail'),
              'body_footer' => trans('g.reset_password_mail_footer'),
              'subcopy' => trans('g.email_trouble_clicking_button', ['actionText' => trans('g.reset_password')]) . ' ' . $this->actionURL,
              'footer' => trans('g.email_ip_address') . ' **' . request()->ip() . '**'
          ]);
    }
}
