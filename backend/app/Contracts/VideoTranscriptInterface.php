<?php

namespace App\Contracts;

interface VideoTranscriptInterface
{
    public function getTranscript(string $videoId): array;
    public function extractVideoId(string $url): ?string;
    public function isValidYouTubeUrl(string $url): bool;
    public function isDIYTranscript(string $transcript): bool;
    public function cleanTranscript(string $transcript): string;
    public function analyzeTranscriptWithOpenRouter(string $transcript): array;
}
