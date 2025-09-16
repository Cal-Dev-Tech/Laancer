<?php

namespace App\Helper;

class BroadcastingHelper
{
    /**
     * Check if broadcasting is properly configured
     *
     * @return bool
     */
    public static function isConfigured(): bool
    {
        $driver = env('BROADCAST_DRIVER', 'null');
        
        if ($driver === 'null') {
            return false;
        }
        
        switch ($driver) {
            case 'pusher':
                return self::isPusherConfigured();
            case 'reverb':
                return self::isReverbConfigured();
            default:
                return false;
        }
    }
    
    /**
     * Check if Pusher is properly configured
     *
     * @return bool
     */
    public static function isPusherConfigured(): bool
    {
        return !empty(env('PUSHER_APP_ID')) 
            && !empty(env('PUSHER_APP_KEY')) 
            && !empty(env('PUSHER_APP_SECRET')) 
            && !empty(env('PUSHER_APP_CLUSTER'));
    }
    
    /**
     * Check if Reverb is properly configured
     *
     * @return bool
     */
    public static function isReverbConfigured(): bool
    {
        return !empty(env('REVERB_APP_ID')) 
            && !empty(env('REVERB_APP_KEY')) 
            && !empty(env('REVERB_APP_SECRET')) 
            && !empty(env('REVERB_HOST'))
            && !empty(env('REVERB_PORT'));
    }
    
    /**
     * Get the current broadcasting driver
     *
     * @return string
     */
    public static function getDriver(): string
    {
        return env('BROADCAST_DRIVER', 'null');
    }
    
    /**
     * Get broadcasting configuration for frontend
     *
     * @return array
     */
    public static function getFrontendConfig(): array
    {
        $driver = self::getDriver();
        
        switch ($driver) {
            case 'pusher':
                return [
                    'driver' => 'pusher',
                    'key' => env('PUSHER_APP_KEY'),
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'forceTLS' => true,
                ];
            case 'reverb':
                return [
                    'driver' => 'reverb',
                    'key' => env('REVERB_APP_KEY'),
                    'wsHost' => env('REVERB_HOST'),
                    'wsPort' => env('REVERB_PORT'),
                    'wssPort' => env('REVERB_PORT'),
                    'forceTLS' => env('REVERB_SCHEME') === 'https',
                    'enabledTransports' => ['ws', 'wss'],
                ];
            default:
                return [];
        }
    }
}