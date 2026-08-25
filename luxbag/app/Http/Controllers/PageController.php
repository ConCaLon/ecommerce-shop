<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'email'   => 'required|email',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        // TODO: Mail::to('contact@luxbag.vn')->send(new ContactMail($request->all()));

        return back()->with('success', 'Tin nhắn của bạn đã được gửi. Chúng tôi sẽ phản hồi trong 24h!');
    }

    public function policy()
    {
        return view('pages.policy');
    }

    public function faq()
    {
        return view('pages.faq');
    }
}
