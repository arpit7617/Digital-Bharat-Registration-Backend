<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $otp)
    {
        $this->name = $name;
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Verification Code - Digital India Yug',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 12px; background-color: #ffffff;'>
                <div style='text-align: center; padding-bottom: 20px; border-bottom: 2px solid #2196F3;'>
                    <h2 style='color: #2196F3; margin: 0;'>Digital India Yug</h2>
                    <p style='color: #666666; font-size: 14px; margin-top: 4px;'>Password Reset Request</p>
                </div>
                <div style='padding: 24px 0;'>
                    <p style='font-size: 16px; color: #333333;'>Hello <strong>" . htmlspecialchars($this->name) . "</strong>,</p>
                    <p style='font-size: 14px; color: #555555; line-height: 1.6;'>
                        We received a request to reset the password for your Digital India Yug account. Use the 6-digit verification code below to set a new password:
                    </p>
                    <div style='text-align: center; margin: 28px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #2196F3; background-color: #E3F2FD; padding: 12px 28px; border-radius: 8px; display: inline-block;'>
                            " . htmlspecialchars($this->otp) . "
                        </span>
                    </div>
                    <p style='font-size: 13px; color: #777777;'>This code will expire in <strong>15 minutes</strong>. If you did not request a password reset, please ignore this email.</p>
                </div>
                <div style='border-top: 1px solid #eeeeee; padding-top: 16px; text-align: center; font-size: 12px; color: #999999;'>
                    &copy; " . date('Y') . " Digital India Yug. All rights reserved.
                </div>
            </div>
            "
        );
    }
}
