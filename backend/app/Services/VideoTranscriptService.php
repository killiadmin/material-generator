<?php

namespace App\Services;

use App\Contracts\VideoTranscriptInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use MrMySQL\YoutubeTranscript\Exception\PoTokenRequiredException;
use MrMySQL\YoutubeTranscript\Exception\YouTubeRequestFailedException;
use MrMySQL\YoutubeTranscript\TranscriptListFetcher;
use Illuminate\Support\Collection;
use Throwable;

/**
 * VideoTranscriptService
 *
 * @implements VideoTranscriptInterface
 */
class VideoTranscriptService implements VideoTranscriptInterface
{
    private Client $httpClient;
    private RequestFactory $requestFactory;
    private StreamFactory $streamFactory;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->requestFactory = new RequestFactory();
        $this->streamFactory = new StreamFactory();
    }

    /**
     * @param string $videoId
     * @return array
     * @throws PoTokenRequiredException
     * @throws YouTubeRequestFailedException
     * @throws Throwable
     */
    public function getTranscript(string $videoId): array
    {
        $fetcher = new TranscriptListFetcher(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );

        $transcriptList = $fetcher->fetch($videoId);
        $transcript = $transcriptList->findTranscript(['fr', 'en']);
        $transcriptText = $transcript->fetch();

        $text = Collection::make($transcriptText)
            ->pluck('text')
            ->join(' ');

        return [
            'video_id' => $videoId,
            'transcript' => $text,
            'debug_info' => [
                'transcript_type' => gettype($transcriptText),
                'text_length' => strlen($text),
                'available_methods' => get_class_methods($transcript)
            ]
        ];
    }

    /**
     * Extractor id video from url
     *
     * @param string $url
     * @return string|null
     */
    public function extractVideoId(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            if (isset($query['v'])) {
                return $query['v'];
            }
        }

        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if url is a valid youtube url
     *
     * @param string $url
     * @return bool
     */
    public function isValidYouTubeUrl(string $url): bool
    {
        return $this->extractVideoId($url) !== null;
    }

    /**
     * Transcribe the video with OpenRouter
     *
     * @throws ConnectionException
     */
    public function analyzeTranscriptWithOpenRouter(string $transcript): array
    {
        $prompt = "
            Voici la transcription d'une vidéo de bricolage :
            \"$transcript\"

            Analyse le texte et fournis deux listes JSON :
                - \"materiaux\" : les matériaux nécessaires
                - \"outils\" : les outils nécessaires
            Réponds uniquement en JSON au format :
            {
                \"materiaux\": [...],
                \"outils\": [...]
            }
        ";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'HTTP-Referer' => url('/'),
            'X-Title' => 'Laravel Bricolage Analyzer',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'mistralai/mistral-7b-instruct',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un assistant spécialisé en bricolage.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 500,
        ]);

        if (!$response->successful()) {
            return [
                'error' => 'Erreur API OpenRouter',
                'status' => $response->status(),
                'body' => $response->body(),
            ];
        }

        $data = $response->json();

        $content = $data['choices'][0]['message']['content'] ?? $data['choices'][0]['text'] ?? null;

        if (!$content) {
            Log::warning('Réponse vide IA', ['data' => $data]);
            return ['error' => 'Pas de contenu généré', 'raw' => $data];
        }

        $content = trim($content);
        $content = preg_replace('/^```(json)?|```$/m', '', $content);

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::warning('Erreur JSON IA', [
                'error' => $e->getMessage(),
                'raw_text' => $content,
            ]);

            return [
                'error' => 'Réponse IA non décodable',
                'raw_text' => $content,
            ];
        }
    }


    /**
     * Check if transcript is DIY
     *
     * @param string $transcript
     * @return bool
     */
    public function isDIYTranscript(string $transcript): bool
    {
        $keywords = config('analyse.keywords');
        $text = mb_strtolower($transcript, 'UTF-8');

        foreach ($keywords as $word) {
            if (str_contains($text, mb_strtolower($word, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }
}
