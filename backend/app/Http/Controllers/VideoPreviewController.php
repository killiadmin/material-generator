<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VideoPreviewController extends Controller
{
    /**
     * Fetches and provides metadata of a YouTube video, such as title, thumbnail, channel name, and video ID,
     * based on the provided video URL.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(Request $request): JsonResponse
    {
        $url = $request->input('url');


        if (!$url || !preg_match('/(?:youtu\.be\/|youtube\.com\/watch\?v=)([^\&\?\/]+)/', $url, $matches)) {
            return response()->json(['error' => 'URL YouTube invalide'], 400);
        }

        $videoId = $matches[1];
        $apiUrl = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=" . $videoId. "&format=json";

        try {
            $response = file_get_contents($apiUrl);

            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            return response()->json([
                'title' => $data['title'],
                'thumbnail' => $data['thumbnail_url'],
                'channelTitle' => $data['author_name'],
                'videoId' => $videoId,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 500);
        }
    }
}
