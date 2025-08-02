<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendFirebaseNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token, $title, $body, $data;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $title, $body, $data = [])
    {
        $this->token = $token;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $serverKey = config('services.firebase.server_key');

        Http::withToken($serverKey)->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $this->token,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
                'sound' => 'default',
            ],
            'data' => $this->data,
            'priority' => 'high',
        ]);
    }

    /**
     * (Optional) Prevent duplicate jobs from overlapping.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping,
        ];
    }
}
