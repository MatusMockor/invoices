<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtToken
{
    /**
     * Generate a JWT token for the scraper service
     */
    public function generateToken(): string
    {
        $secret = config('services.scraper.jwt_secret');
        $algorithm = config('services.scraper.jwt_algorithm', 'HS256');

        $issuedAt = now()->timestamp;
        $expire = now()->addHour()->timestamp;

        $payload = [
            'iss' => config('app.name'),  // Issuer
            'iat' => $issuedAt,           // Issued at
            'exp' => $expire,             // Expiration time
            'sub' => 'scraper-api',        // Subject
        ];

        return JWT::encode($payload, $secret, $algorithm);
    }

    /**
     * Validate a JWT token
     */
    public function validateToken(string $token): bool
    {
        try {
            $secret = config('services.scraper.jwt_secret');
            $algorithm = config('services.scraper.jwt_algorithm', 'HS256');

            JWT::decode($token, new Key($secret, $algorithm));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
