<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VPNMailController extends Mailable
{
    use Queueable, SerializesModels;
    protected $postparam;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($postparam)
    {
        $this->postparam = $postparam;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
                return $this->from('provision@neo.id','provision@neo.id')
                        ->view('mailvpn')
                        ->subject("NEO Metal VPN Access")
                   ->with(
                    [
                        'username' => $this->postparam[0],
                        'password' => $this->postparam[1]
                    ]);        
    }
}
