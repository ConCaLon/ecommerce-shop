<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $appName = Setting::get('site_name', config('app.name'));

        return (new MailMessage)
            ->from(config('mail.from.address'), $appName)
            ->subject('Xác thực tài khoản mua hàng - '.$appName)
            ->view([
                'html' => 'emails.verify_email',
                'text' => 'emails.verify_email_text',
            ], [
                'user' => $notifiable,
                'verificationUrl' => $this->verificationUrl($notifiable),
                'expiresInMinutes' => config('auth.verification.expire'),
                'appName' => $appName,
            ]);
    }
}
