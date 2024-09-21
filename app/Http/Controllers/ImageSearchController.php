<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageSearchController extends Controller
{
    public function search(Request $request)
    {
        // Validate the image file
        $validator = Validator::make($request->all(), [
            'imageFile' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate that it's an image
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Process the uploaded image
        if ($request->hasFile('imageFile')) {
            $imageFile = $request->file('imageFile');
            $filePath = $imageFile->store('uploads', 'public'); // Store the image in the public/uploads folder

            // Simulate an image search based on the uploaded image
            return response()->json(['message' => "Image successfully uploaded and stored at: $filePath"]);
        }

        // If no file is provided, return an error
        return response()->json(['message' => 'Please upload an image.'], 422);
    }
}
