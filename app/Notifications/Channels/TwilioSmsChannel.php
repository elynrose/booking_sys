<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class TwilioSmsChannel
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
        $this->fromNumber = config('services.twilio.phone_number');
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->sms_notifications_enabled || !$notifiable->phone_number) {
            return;
        }

        $message = $notification->toTwilioSms($notifiable);

        try {
            $this->client->messages->create(
                $notifiable->phone_number,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Twilio SMS failed: ' . $e->getMessage(), [
                'user_id' => $notifiable->id,
                'phone' => $notifiable->phone_number,
                'message' => $message
            ]);
        }
    }
} 