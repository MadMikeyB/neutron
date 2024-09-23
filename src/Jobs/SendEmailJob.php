<?php

namespace Neutron\Jobs;

use Neutron\Queue\Job;

class SendEmailJob extends Job
{
    protected string $email;
    protected string $message;

    public function __construct(string $email, string $message)
    {
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Handle the job (simulate sending email).
     */
    public function handle(): void
    {
        // Simulate sending an email
        echo "Sending email to {$this->email} with message: {$this->message}\n";
    }
}