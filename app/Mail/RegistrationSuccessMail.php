<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Registration;

class RegistrationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param Registration $user
     */
    public function __construct(Registration $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('Welcome to Digital India Yug - Successful Registration')
                     ->view('emails.registration_success');

        // Determine category-specific Terms & Conditions PDF
        $category = strtolower($this->user->category ?? '');
        $isStudentOrJobSeeker = str_contains($category, 'student') || str_contains($category, 'job seeker') || str_contains($category, 'jobseeker');

        if ($isStudentOrJobSeeker) {
            $pdfPath = storage_path('app/legal/Employment_Assistance_Terms_And_Conditions.pdf');
            $asName = 'Employment_Assistance_Terms_And_Conditions.pdf';
        } else {
            $pdfPath = storage_path('app/legal/Business_Assistance_Terms_And_Conditions.pdf');
            $asName = 'Business_Assistance_Terms_And_Conditions.pdf';
        }

        if (file_exists($pdfPath)) {
            $mail->attach($pdfPath, [
                'as' => $asName,
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
