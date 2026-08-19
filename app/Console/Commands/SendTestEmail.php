<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Registration;
use App\Mail\RegistrationSuccessMail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:test-email {email} {--category=Student}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a real test registration email to the specified recipient';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('email');
        $this->info("Preparing test email for: {$recipient}...");

        $driver = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $username = config('mail.mailers.smtp.username');

        $this->info("Current Mail Driver: {$driver}");
        if ($driver === 'smtp') {
            $this->info("SMTP Server: {$host}:{$port} (User: {$username})");
        } else {
            $this->warn("Note: Currently MAIL_MAILER={$driver}. (Change MAIL_MAILER=smtp in .env to send real emails to inbox).");
        }

        $selectedCategory = $this->option('category') ?: 'Student';
        $dummyUser = Registration::first();
        if (!$dummyUser) {
            $dummyUser = new Registration([
                'name' => 'Test User',
                'email' => $recipient,
                'mobile' => '9876543210',
                'category' => $selectedCategory,
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'created_at' => now(),
            ]);
        } else {
            $dummyUser->email = $recipient;
            $dummyUser->category = $selectedCategory;
        }

        try {
            Mail::to($recipient)->send(new RegistrationSuccessMail($dummyUser));
            $this->info("SUCCESS: Registration test email successfully sent to {$recipient}!");
            if ($driver === 'log') {
                $this->info("Check storage/logs/laravel.log to view the logged email content.");
            }
        } catch (\Exception $e) {
            $this->error("FAILED to send email: " . $e->getMessage());
        }
    }
}
