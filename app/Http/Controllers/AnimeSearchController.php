<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Use this for making HTTP requests
use App\Http\Controllers\Controller;

class AnimeSearchController extends Controller
{
    public function searchAnime(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'image' => 'required|image|max:2048', // Validate the image input
        ]);

        // Get the uploaded image
        $image = $request->file('image');

        // Call the function that sends the image to the trace.moe API
        $response = $this->searchAnimeByImage($image);

        // Return the response as JSON
        return response()->json($response);
    }

    private function searchAnimeByImage($image)
    {
        // Convert the image to base64 for the API request
        $imageData = base64_encode(file_get_contents($image->getRealPath()));

        // Call the trace.moe API
        $response = Http::post('https://api.trace.moe/search', [
            'image' => $imageData,
        ]);

        // Check if the response is successful
        if ($response->successful()) {
            // Return the API response data
            return $response->json();
        } else {
            // Handle the error response
            return [
                'error' => 'Failed to fetch anime details',
                'details' => $response->json(),
            ];
        }
    }
}
