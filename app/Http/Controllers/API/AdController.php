<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
    /**
     * Display a listing of all ad images.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all ads from the database
        $ads = Ad::all();

        // Transform ads to include full image URLs
        $ads = $ads->map(function ($ad) {
            return [
                'id' => $ad->id,
                'image_url' => asset('storage/app/public/' . $ad->image), // Generate full URL to the image
                'created_at' => $ad->created_at,
                'updated_at' => $ad->updated_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Advertisements retrieved successfully',
            'data' => $ads
        ], 200);
    }
}