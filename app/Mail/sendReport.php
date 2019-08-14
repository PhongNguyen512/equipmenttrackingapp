<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendReport extends Mailable
{
    use Queueable, SerializesModels;
    public $siteName;
    public $date;
    public $fileName;
    public $sendFrom;

    public function __construct($sendFrom, $siteName, $date, $fileName)
    {
        $this->sendFrom = $sendFrom;
        $this->siteName = $siteName;
        $this->date = $date;
        $this->fileName = $fileName;
    }

    public function build()
    {
        return $this->from($this->sendFrom)
                    ->subject('Report of '.$this->siteName.' Equipment on '.$this->date)
                    ->view('email.sendReport') 
                    ->attach('../public/'.$this->fileName);
    }
}
