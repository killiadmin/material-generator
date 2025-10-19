<?php

namespace App\Services;

use App\Contracts\VideoTranscriptInterface;
use GuzzleHttp\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use MrMySQL\YoutubeTranscript\Exception\PoTokenRequiredException;
use MrMySQL\YoutubeTranscript\Exception\YouTubeRequestFailedException;
use MrMySQL\YoutubeTranscript\TranscriptListFetcher;
use Illuminate\Support\Collection;
use Throwable;

/**
 *
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
     * @param string $url
     * @return bool
     */
    public function isValidYouTubeUrl(string $url): bool
    {
        return $this->extractVideoId($url) !== null;
    }
}
