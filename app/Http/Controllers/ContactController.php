<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

class ContactController extends Controller
{
	public function contact(){
                return view('/contact');
	}
    	public function contactPost(Request $request){
        	$this->validate($request, [
                        'name' => 'required',
                        'email' => 'required|email',
                        'comment' => 'required'
                ]);

        	Mail::send('email', [
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'comment' => $request->get('comment') ],
                function ($message) {
                        $message->from('provisioning@neo.id');
                        $message->to('jecky.mu@gmail.com', 'Jecky')
                        ->subject('NEO Metal Support Inquiry');
        	});

        	return back()->with('success', 'Thanks for contacting us, I will get back to you soon!');

    	}	
}
