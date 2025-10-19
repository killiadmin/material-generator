<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Contracts\VideoTranscriptInterface;
use Illuminate\Routing\Controller;
use MrMySQL\YoutubeTranscript\Exception\NoTranscriptFoundException;
use MrMySQL\YoutubeTranscript\Exception\TranscriptsDisabledException;

class VideoTranscriptController extends Controller
{
    public function __construct(
        private VideoTranscriptInterface $transcriptService
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchTranscript(Request $request): JsonResponse
    {
        $url = $request->input('url');

        if (!$this->transcriptService->isValidYouTubeUrl($url)) {
            return response()->json([
                'error' => 'URL vidéo YouTube invalide'
            ], 400);
        }

        $videoId = $this->transcriptService->extractVideoId($url);

        try {
            $transcriptData = $this->transcriptService->getTranscript($videoId);

            return response()->json($transcriptData);

        } catch (NoTranscriptFoundException $e) {
            \Log::error("NoTranscriptFoundException", [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Aucune transcription trouvée pour cette vidéo.'], 404);

        } catch (TranscriptsDisabledException $e) {
            \Log::error("TranscriptsDisabledException", [
                'video_id' => $videoId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Les transcriptions sont désactivées pour cette vidéo.'], 403);

        } catch (\Exception $e) {
            \Log::error("Exception générale", [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json(['error' => 'Erreur lors de la récupération de la transcription.'], 500);
        }
    }
}
