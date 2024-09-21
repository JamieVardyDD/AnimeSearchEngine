<?php

namespace App\Services;

use GuzzleHttp\Client;

class TraceMoeService
{
    protected $client;
    protected $apiUrl = 'https://api.trace.moe/search';
    protected $apiKey = 'YOUR_TRACE_MOE_API_KEY'; // Replace with your actual API key

    public function __construct()
    {
        // Initialize Guzzle client
        $this->client = new Client();
    }

    /**
     * Search anime by image
     *
     * @param string $imageBase64
     * @return array
     */
    public function searchAnimeByImage($imageBase64)
    {
        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                    'image' => $imageBase64, // Image in Base64 format
                ]
            ]);

            // Parse the JSON response
            $responseData = json_decode($response->getBody(), true);
            return $responseData;

        } catch (\Exception $e) {
            // Handle error
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
