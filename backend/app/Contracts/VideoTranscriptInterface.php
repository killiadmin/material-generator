<?php

namespace App\Contracts;

interface VideoTranscriptInterface
{
    public function getTranscript(string $videoId): array;
    public function extractVideoId(string $url): ?string;
    public function isValidYouTubeUrl(string $url): bool;
}
