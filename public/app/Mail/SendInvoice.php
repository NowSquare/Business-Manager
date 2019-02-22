<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sender_name, $sender_email, $company_name, $invoice, $name, $email)
    {
        $this->sender_name = $sender_name;
        $this->sender_email = $sender_email;
        $this->company_name = $company_name;
        $this->invoice = $invoice;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->markdown('emails.message')
          ->from($this->sender_email, $this->sender_name)
          ->subject(trans('g.send_invoice_subject', ['invoice_number' => $this->invoice->reference, 'company_name' => $this->company_name]))
          ->with([
              'header' => config('system.name'),
              'sender_name' => $this->sender_name,
              'sender_email' => $this->sender_email,
              'sender_company' => $this->company_name,
              'salutation' => trans('g.email_salutation', ['name' => $this->name]),
              'body_top' => trans('g.send_invoice_mail', ['company_name' => $this->company_name]),
              'body_footer' => trans('g.send_invoice_mail_footer'),
              'footer' => trans('g.email_ip_address') . ' **' . request()->ip() . '**'
          ]);

        $file = storage_path('app/invoices/' . $this->invoice->id . '.pdf');

        if (\File::exists($file)) {          
          $filename = 'invoice-' . str_slug($this->invoice->reference . '-' . $this->company_name . '-' . auth()->user()->formatDate($this->invoice->issue_date, 'date_medium')) . '.pdf';

          $email->attach($file, [
              'as' => $filename,
              'mime' => 'application/pdf',
          ]);
        }

        return $email;
    }
}
