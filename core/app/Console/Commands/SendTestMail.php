<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test
                            {email : Recipient email address}
                            {--subject= : Subject line for the test email}
                            {--message= : Message body for the test email}
                            {--mailer= : Override mailer (defaults to mail.default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email using the current mail configuration from .env';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = (string) $this->argument('email');
        $subject = (string) ($this->option('subject') ?: 'Laravel Mail Test');
        $message = (string) ($this->option('message') ?: 'This is a test email to verify outgoing mail configuration.');
        $selectedMailer = (string) ($this->option('mailer') ?: config('mail.default'));

        $fromAddress = (string) (config('mail.from.address') ?: 'no-reply@example.com');
        $fromName = (string) (config('mail.from.name') ?: config('app.name', 'Laravel'));

        $this->info('Preparing to send test email...');
        $this->line('Mailer: ' . $selectedMailer);

        $host = (string) (config('mail.mailers.' . $selectedMailer . '.host') ?: 'n/a');
        $port = (string) (config('mail.mailers.' . $selectedMailer . '.port') ?: 'n/a');
        $transport = (string) (config('mail.mailers.' . $selectedMailer . '.transport') ?: 'n/a');
        $encryption = (string) (config('mail.mailers.' . $selectedMailer . '.encryption') ?: 'n/a');

        $this->line('Transport: ' . $transport);
        $this->line('Host: ' . $host . '  Port: ' . $port . '  Encryption: ' . $encryption);
        $this->line('From: ' . $fromName . ' <' . $fromAddress . '>');
        $this->line('To: ' . $recipient);

        try {
            $mailer = Mail::mailer($selectedMailer);

            $mailer->raw($message, function ($mail) use ($recipient, $subject, $fromAddress, $fromName) {
                $mail->to($recipient)
                    ->from($fromAddress, $fromName)
                    ->subject($subject);
            });

            $this->info('Test email dispatched successfully. Check the recipient inbox and any mail logs.');
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}


