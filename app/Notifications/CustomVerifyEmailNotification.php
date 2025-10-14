<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        
        // Extract the ID and hash from the original parameters
        $id = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification());
        
        // Generate the signed URL
        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $id,
                'hash' => $hash,
            ]
        );

        // Parse the signed URL to get the query parameters
        $parsedUrl = parse_url($temporarySignedURL);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        // Combine all parameters for the frontend URL
        $params = [
            'id' => $id,
            'hash' => $hash,
            'expires' => $queryParams['expires'] ?? '',
            'signature' => $queryParams['signature'] ?? '',
        ];

        // Build the complete frontend URL
        return $frontendUrl . '/auth/email/verify?' . http_build_query($params);
    }
} 