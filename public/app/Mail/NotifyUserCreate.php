<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserCreate extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($actionURL, $creator, $name, $email, $password)
    {
        $this->actionURL = $actionURL;
        $this->creator = $creator;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
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
          ->subject(trans('g.account_created'))
          ->with([
              'actionURL' => $this->actionURL,
              'actionText' => trans('g.activate_account'),
              'header' => config('system.name'),
              'salutation' => trans('g.email_salutation', ['name' => $this->name]),
              'body_top' => trans('g.account_created_mail', ['creator' => $this->creator]),
              'body_footer' => trans('g.account_created_mail_footer', ['email' => $this->email, 'password' => $this->password]),
              'subcopy' => trans('g.email_trouble_clicking_button', ['actionText' => trans('g.activate_account')]) . ' ' . $this->actionURL,
              'footer' => trans('g.email_ip_address') . ' **' . request()->ip() . '**'
          ]);
    }
}
