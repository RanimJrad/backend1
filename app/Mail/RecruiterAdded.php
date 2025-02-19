<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruiterAdded extends Mailable
{
    use Queueable, SerializesModels;

    public $recruiterName;
    public $password;

    public function __construct($recruiterName, $password)
    {
        $this->recruiterName = $recruiterName;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Nouvel utilisateur recruteur ajouté')
                    ->view('emails.recruiterAdded')
                    ->with([
                        'recruiterName' => $this->recruiterName,
                        'password' => $this->password,
                    ]);
    }
}
