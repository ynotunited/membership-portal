<?php

namespace App\Helpers;

/**
 * Lightweight Paystack API client using raw cURL.
 */
class PaystackService
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? '';
    }

    public function initializeTransaction(string $email, int $amount, string $reference, array $metadata = [])
    {
        $url = "https://api.paystack.co/transaction/initialize";

        $data = [
            'email' => $email,
            'amount' => $amount * 100, // Paystack uses kobo (cents)
            'reference' => $reference,
            'metadata' => $metadata
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    public function verifyTransaction(string $reference)
    {
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        return $this->makeRequest('GET', $url);
    }

    private function makeRequest(string $method, string $url, array $data = [])
    {
        if (empty($this->secretKey)) {
            throw new \Exception("Paystack secret key is missing");
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            "Authorization: Bearer {$this->secretKey}",
            "Cache-Control: no-cache"
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = "Content-Type: application/json";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local dev
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new \Exception("cURL Error: " . $err);
        }

        return json_decode($response, true);
    }
}
