<?php

namespace App\Services;

class DocumentClassifierService
{
    /**
     * Klasifikasikan satu teks (satu halaman atau satu dokumen utuh).
     *
     * @return array{category: ?string, label: string, confidence: float, scores: array<string, int>}
     */
    public function classify(string $text): array
    {
        $categories = config('document_categories.categories');
        $scores = $this->score($text);

        // max() + array_search mengambil kategori pertama di config kalau skornya seri
        $bestScore = max($scores);
        $bestCategory = array_search($bestScore, $scores, true);

        if ($bestScore === 0) {
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
            'confidence' => round($bestScore / array_sum($scores), 2),
            'scores' => $scores,
        ];
    }

    /**
     * Klasifikasikan per halaman. Halaman yang tidak dikenali (kosong, gagal OCR)
     * ikut kategori halaman sebelumnya, atau halaman sesudahnya kalau ada di awal.
     *
     * @param  list<string>  $pages
     * @return list<array{category: ?string, label: string, confidence: float, scores: array<string, int>}>
     */
    public function classifyPages(array $pages): array
    {
        $results = array_map(fn (string $page) => $this->classify($page), $pages);

        $previous = null;
        foreach ($results as $i => $result) {
            if ($result['category'] === null && $previous !== null) {
                $results[$i] = $this->inherit($result, $previous);
            }

            $previous = $results[$i]['category'] !== null ? $results[$i] : $previous;
        }

        $next = null;
        for ($i = count($results) - 1; $i >= 0; $i--) {
            if ($results[$i]['category'] === null && $next !== null) {
                $results[$i] = $this->inherit($results[$i], $next);
            }

            $next = $results[$i]['category'] !== null ? $results[$i] : $next;
        }

        return $results;
    }

    /**
     * Skor tiap kategori untuk sebuah teks, urut sesuai config.
     *
     * @return array<string, int>
     */
    public function score(string $text): array
    {
        $config = config('document_categories');
        $text = ' '.$this->normalize($text).' ';
        $scores = array_fill_keys(array_keys($config['categories']), 0);

        foreach ($config['ignore'] as $phrase) {
            $text = $this->removePhrase($text, $phrase);
        }

        // Frasa terpanjang dulu, supaya "loading order" tidak ikut terhitung sebagai "loading"
        $phrases = [];
        foreach ($config['categories'] as $key => $category) {
            foreach ($category['titles'] as $title) {
                $phrases[] = [$key, $this->normalize($title), true];
            }
            foreach ($category['keywords'] as $keyword) {
                $phrases[] = [$key, $this->normalize($keyword), false];
            }
        }
        usort($phrases, fn (array $a, array $b) => mb_strlen($b[1]) <=> mb_strlen($a[1]));

        foreach ($phrases as [$key, $phrase, $isTitle]) {
            $count = preg_match_all($this->phrasePattern($phrase), $text);

            if ($count > 0) {
                $scores[$key] += $isTitle ? $config['title_weight'] : $count;
                $text = $this->removePhrase($text, $phrase);
            }
        }

        foreach ($config['categories'] as $key => $category) {
            foreach ($category['patterns'] as $pattern) {
                $scores[$key] += preg_match_all($pattern, $text);
            }
        }

        return $scores;
    }

    /**
     * Huruf kecil, hanya huruf/angka/garis miring, spasi tunggal.
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s\/]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        // Judul berhuruf renggang seperti "M A N I F E S T" disatukan jadi "manifest"
        $text = preg_replace_callback(
            '/\b(?:[a-z] ){3,}[a-z]\b/',
            fn (array $match) => str_replace(' ', '', $match[0]),
            $text
        );

        return trim($text);
    }

    /**
     * Cocok di awal kata, akhiran bebas: "discharge" juga cocok dengan "discharged",
     * "document" dengan "documents".
     */
    private function phrasePattern(string $phrase): string
    {
        return '/\b'.str_replace('\ ', ' ', preg_quote($phrase, '/')).'[a-z]*/';
    }

    private function removePhrase(string $text, string $phrase): string
    {
        return preg_replace($this->phrasePattern($this->normalize($phrase)), ' ', $text);
    }

    /**
     * @param  array{category: ?string, label: string, confidence: float, scores: array<string, int>}  $result
     * @param  array{category: ?string, label: string, confidence: float, scores: array<string, int>}  $from
     * @return array{category: ?string, label: string, confidence: float, scores: array<string, int>}
     */
    private function inherit(array $result, array $from): array
    {
        return array_merge($result, [
            'category' => $from['category'],
            'label' => $from['label'],
        ]);
    }
}
