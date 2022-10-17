<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use App\Mail\SendEmailTest;
// use Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $email;
    protected $template;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $template)
    {
        $this->email = $email;
        $this->template = $template;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send($this->template);
        // try {
        //     Mail::to($this->email)->send($this->template);
        // } catch (\Throwable $exception) {
        //     if ($this->attempts() > 3) {
        //         // hard fail after 4 attempts
        //         throw $exception;
        //     }

        //     // requeue this job to be executes
        //     // in 1 minutes (60 seconds) from now
        //     $this->release(10);
        //     return;
        // }
    }

    public function retryUntil()
    {
        // will keep retrying, by backoff logic below
        // until 12 hours from first run.
        // After that, if it fails it will go
        // to the failed_jobs table
        return now()->addHours(1);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff()
    {
        // first 4 retries, after first failure
        // will be 4 minutes (240 seconds) apart,
        // further attempts will be
        // 3 hours (10,800 seconds) after
        // previous attempt
        return [60, 60, 60, 3600];
    }
}
