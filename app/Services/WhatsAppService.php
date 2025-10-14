<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendMessage(string $phone, string $message): bool
    {
        // Example API call (adjust to your WhatsApp API provider)
        $response = Http::post('https://api.whatsappprovider.com/send', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }
}
