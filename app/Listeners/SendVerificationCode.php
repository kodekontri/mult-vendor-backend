<?php

namespace App\Listeners;

use App\Events\NewAccountRegistered;
use Illuminate\Support\Facades\Mail;

class SendVerificationCode
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  NewAccountRegistered  $event
     * @return void
     */
    public function handle(NewAccountRegistered $event)
    {
        $token = md5(rand(1000,34000));
        $event->user->verification_code = md5($token);
        $event->user->save();

        Mail::send("mail.mailverification",['user'=> $event->user, 'token' => $token],function($message) use ($event){
            $message->to($event->user->email)->subject('Welcome');
        });
    }
}
