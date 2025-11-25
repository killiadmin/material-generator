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
     * Get fake transcript from json file
     *
     * @param string $videoId
     * @return string[]
     */
    public function getFakeTranscript (string $videoId) :array {
        sleep(3);

        $path = storage_path('app/public/data/list.json');

        if (!file_exists($path)) {
            return ["error" => "Fichier introuvable"];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return ["error" => "JSON invalide"];
        }

        foreach ($data as $item) {
            if (isset($item['video_id']) && $item['video_id'] === $videoId) {
                return $item;
            }
        }

        return ["error" => "Aucune donnée trouvée pour cette vidéo"];
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
        $prompt = <<<EOT
            Tu es un expert en bricolage.
            Voici la transcription complète d'une vidéo de bricolage :

            ---
            $transcript
            ---

            Analyse attentivement le contenu.
            Ton objectif est d'extraire les informations suivantes :

            1. **Matériaux (materiaux)** : tous les éléments consommables ou assemblables (ex : bois, vis, colle, peinture, planches, clous, ruban adhésif, etc.).
            2. **Outils (outils)** : tous les instruments nécessaires pour réaliser le projet (ex : tournevis, perceuse, marteau, pince, scie, cutter, règle, etc.).

            Règles importantes :
            - Déduis les éléments implicites si le texte les suggère (ex : "j'ai percé un trou" → perceuse).
            - Ne mélange pas outils et matériaux.
            - N’ajoute pas d’explication ni de texte hors JSON.
            - Supprime les doublons.
            - Utilise des noms simples, au singulier.
            - Réponds uniquement en JSON valide, au format strict suivant :

            {
            "materiaux": ["..."],
            "outils": ["..."]
            }

            Si une des listes est vide, retourne une liste vide [].
            EOT;

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
            'temperature' => 0.2,
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

    /**
     * CLeaned transcript from stop words
     *
     * @param string $transcript
     * @return string
     */
    public function cleanTranscript(string $transcript): string
    {
        $stopWords = config('analyse.stop_words');
        $text = mb_strtolower($transcript, 'UTF-8');
        $text = preg_replace('/[^\w\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text);
        $filteredWords = array_diff($words, $stopWords);
        $cleanedText = implode(' ', $filteredWords);
        $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);

        return trim($cleanedText);
    }
}
