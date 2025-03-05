<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class forgetPasswordEmail extends Mailable
{
  use SerializesModels;

  public $subject = "Password Reset Request";

  // Data to pass to the view (if any)
  public $token;
  public $user;

  // Constructor to accept data
  public function __construct($token, $user)
  {
    $this->token = $token;
    $this->user = $user;
  }

  public function build()
  {
    return $this->view('email-format.forgotpass-mail') // Blade template for the email content
      ->with([
        'url' => route('reset-password', ['token' => $this->token]),
        'name' => $this->user->name,
        'email' => $this->user->email,
      ])
      ->subject($this->subject);
  }
}
