<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $name;

    // 🔧 تعديل: إضافة استقبال الرمز والاسم بدون تغيير اسم الكلاس
    // --------------------------------------------
    public function __construct(string $otp, string $name)
    {
        $this->otp = $otp;
        $this->name = $name;
    }
    // --------------------------------------------

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    public function content(): Content
    {
        // 🔧 تعديل: تحديد View الصحيح للإيميل
        // --------------------------------------------
        return new Content(
            view: 'emails.otp',
        );
        // --------------------------------------------
    }

    public function attachments(): array
    {
        return [];
    }
}
