<?php

namespace App\Services;

class BotService
{
    public static function generateReply(string $message): string
    {
        $message = strtolower(trim($message));

        if (self::contains($message, ['password', 'forgot'])) {
            return "To reset your password, please go to your Profile and tap on 'Change Password'.";
        }

        if (self::contains($message, ['status', 'application', 'track'])) {
            return "You can check the status of your applications under the 'Services' tab in your dashboard.";
        }

        if (self::contains($message, ['category', 'change role', 'wrong type'])) {
            return "Categories (e.g., Farmer, Business) cannot be changed after registration. You will need to register a new account to change your category.";
        }

        if (self::contains($message, ['loan', 'money', 'credit'])) {
            return "We offer various loan services in the Services tab. Please navigate there to apply.";
        }

        if (self::contains($message, ['hello', 'hi', 'hey', 'start'])) {
            return "Hello there! I am the Digital Registration Automated Bot. How can I assist you today?";
        }

        if (self::contains($message, ['thank', 'thanks'])) {
            return "You're very welcome! Let me know if you need anything else.";
        }

        // Default reply
        return "I am a digital automated bot! Your message has been logged. I currently only know answers to basic questions like password resets, application status, and category changes.";
    }

    private static function contains(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }
}
