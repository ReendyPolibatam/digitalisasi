<?php

namespace App\Services;

class DocumentClassifierService
{
    public function classify(string $text): array
    {
        $normalizedText = $this->normalize($text);
        $categories = config('document_categories');
        $scores = [];

        foreach ($categories as $key => $category) {
            $matchCount = 0;
            $totalKeywords = count($category['keywords']);

            foreach ($category['keywords'] as $keyword) {
                if (str_contains($normalizedText, $this->normalize($keyword))) {
                    $matchCount++;
                }
            }

            $scores[$key] = $totalKeywords > 0 ? $matchCount / $totalKeywords : 0;
        }

        $bestCategory = null;
        $bestScore = 0;

        foreach ($scores as $key => $score) {
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCategory = $key;
            }
        }

        if ($bestScore == 0) {
            return [
                'category' => null,
                'label' => 'Tidak Terklasifikasi',
                'confidence' => 0.0,
                'scores' => $scores,
            ];
        }

        return [
            'category' => $bestCategory,
            'label' => $categories[$bestCategory]['label'],
            'confidence' => round($bestScore, 2),
            'scores' => $scores,
        ];
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s\/]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}