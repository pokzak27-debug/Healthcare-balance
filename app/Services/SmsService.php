<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $sid = config('twilio.sid');
        $token = config('twilio.token');
        $from = config('twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::info('SMS non envoyé (Twilio non configuré) : ' . substr($message, 0, 50) . '...');

            return false;
        }

        try {
            if (! class_exists(\Twilio\Rest\Client::class)) {
                Log::info('SMS non envoyé : package twilio/sdk non installé');

                return false;
            }
            $client = new \Twilio\Rest\Client($sid, $token);
            $client->messages->create($this->formatPhone($to), [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Erreur envoi SMS : ' . $e->getMessage());

            return false;
        }
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+33' . substr($phone, 1);
        }
        if (! str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}
