<?php

namespace App\Services;

use App\Models\PhoneOtp;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WhatsAppOtpService
{
    public function send(string $phone): void
    {
        $config = config('services.meta_whatsapp');
        $required = ['access_token', 'phone_number_id', 'template_name'];

        foreach ($required as $key) {
            if (blank($config[$key] ?? null)) {
                throw new RuntimeException('WhatsApp OTP delivery is not configured.');
            }
        }

        $otp = (string) random_int(100000, 999999);

        $recipient = ($config['country_code'] ?? '91').$phone;
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $config['api_version'] ?? 'v23.0',
            $config['phone_number_id']
        );

        try {
            $response = Http::withToken($config['access_token'])
                ->acceptJson()
                ->timeout(10)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $recipient,
                    'type' => 'template',
                    'template' => [
                        'name' => $config['template_name'],
                        'language' => [
                            'code' => $config['template_language'] ?? 'en_US',
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $otp,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            Log::error('Meta WhatsApp OTP delivery failed to connect.', [
                'exception' => $exception::class,
            ]);

            throw new RuntimeException('Unable to send an OTP right now.', previous: $exception);
        }

        if (! $response->successful()) {
            Log::error('Meta WhatsApp OTP delivery was rejected.', [
                'status' => $response->status(),
            ]);

            throw new RuntimeException('Unable to send an OTP right now.');
        }

        PhoneOtp::updateOrCreate(
            ['phone' => $phone],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
            ]
        );
    }

    public function consume(string $phone, string $otp): bool
    {
        return DB::transaction(function () use ($phone, $otp): bool {
            $record = PhoneOtp::where('phone', $phone)->lockForUpdate()->first();

            if (! $record || $record->expires_at->isPast() || ! Hash::check($otp, $record->otp)) {
                return false;
            }

            $record->delete();

            return true;
        });
    }
}
