<?php

namespace Modules\Newsletters\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($from_name, $from_email, $subject, $style, $content, $unsubscribe_url)
    {
        $this->from_name = $from_name;
        $this->from_email = $from_email;
        $this->subject = $subject;
        $this->style = $style;
        $this->content = $content;
        $this->unsubscribe_url = $unsubscribe_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->view('newsletters::emails.newsletter')
          ->from($this->from_email, $this->from_name)
          ->subject($this->subject)
          ->with([
              'style' => $this->style,
              'content' => $this->content,
              'unsubscribe_url' => $this->unsubscribe_url
          ]);

        return $email;
    }
}
