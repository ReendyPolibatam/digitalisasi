<?php

namespace App\Services;

class DocumentFieldExtractorService
{
    public function extract(string $text): array
    {
        return [
            'vessel_name' => $this->extractVesselName($text),
            'voyage' => $this->extractVoyage($text),
            'loading_date' => $this->extractDate($text, [
                'loading commenced',
                'date of loading',
                'loading completed',
            ]),
            'discharge_date' => $this->extractDate($text, [
                'discharge commenced',
                'discharging commenced',
                'discharge completed',
                'date of discharge',
            ]),
            'bl_liters_obs' => $this->extractBlLitersObs($text),
            'liters_15c' => $this->extractLiters15C($text),
        ];
    }

    // Awalan kapal pengangkut muatan (tongkang, oil barge, tanker, dst.)
    private const VESSEL_PREFIXES = 'SPOB|TKG|OB|MT|MV|KM|LCT|BG';

    // Kapal tunda (tug boat): stempelnya ada di banyak halaman, tapi bukan kapal muatannya
    private const TUG_PREFIXES = 'TB';

    // Kata yang menandai akhir nama kapal (label kolom berikutnya di baris yang sama)
    private const VESSEL_STOP_WORDS = 'PORT|NATIONALITY|VOYAGE|DATE|NO|FLAG|ARRIVED|SAILED|LOADING|DISCHARG\w*|MASTER|GRADE|CARGO|CONSIGN\w*|ON|AT|IN|HAS';

    /**
     * Nama kapal dari label ("Name of Vessel : ...") kalau terbaca, kalau tidak dari
     * nama berawalan jenis kapal (OB, TKG, ...) yang paling sering muncul.
     * Kapal tunda (TB) hanya dipakai kalau tidak ada nama kapal muatan sama sekali.
     */
    private function extractVesselName(string $text): ?string
    {
        return $this->vesselFromLabel($text)
            ?? $this->vesselFromPrefix($text, self::VESSEL_PREFIXES)
            ?? $this->vesselFromPrefix($text, self::TUG_PREFIXES);
    }

    private function vesselFromLabel(string $text): ?string
    {
        $label = '(?:name\W{0,3}s?\W{0,3}of\s+vessel|vessel\s+name|vessel(?:\s+no)?|to\s+the\s+master\s+of)';

        preg_match_all('/'.$label.'\s*[:;]\s*(.+)$/im', $text, $matches);

        foreach ($matches[1] as $value) {
            $name = $this->cleanVesselName($value);

            if ($name !== null) {
                return $name;
            }
        }

        return null;
    }

    private function vesselFromPrefix(string $text, string $prefixes): ?string
    {
        // \h (bukan \s) supaya nama tidak menyambung ke baris berikutnya
        preg_match_all(
            '/\b(?:'.$prefixes.')[.,]?\h+(?:PT\.?\h+)?[A-Za-z]{3,}(?:\h+[A-Za-z]{3,}){0,3}/',
            $text,
            $matches
        );

        $names = collect($matches[0])
            ->map(fn (string $value) => $this->cleanVesselName($value))
            ->filter();

        if ($names->isEmpty()) {
            return null;
        }

        // Paling sering muncul menang; kalau seri, yang muncul lebih dulu
        $counts = $names->countBy(fn (string $name) => preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($name)));
        $bestKey = $counts->sortDesc()->keys()->first();

        return $names->first(fn (string $name) => preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($name)) === $bestKey);
    }

    /**
     * Potong nilai di label kolom berikutnya, rapikan, dan tolak hasil yang bukan nama.
     */
    private function cleanVesselName(string $value): ?string
    {
        $value = preg_split('/\s{2,}|\s[—–-]+\s|\s+(?:'.self::VESSEL_STOP_WORDS.')\b/i', trim($value))[0];
        $value = trim($value, " \t.,:;-—–_|'\"");

        // Salah baca OCR yang umum untuk awalan "OB": O8, 0B, O08, ...
        $value = preg_replace('/^[O0]{1,2}[8B]\b/i', 'OB', $value);

        $letters = preg_match_all('/[A-Za-z]/', $value);

        if ($letters < 4 || mb_strlen($value) > 40 || ! preg_match('/[A-Za-z]{2,}/', $value)) {
            return null;
        }

        return $value;
    }

    private function extractVoyage(string $text): ?string
    {
        $patterns = [
            '/VOYAGE\s*(?:No\.?|NO)?\s*[:\/]?\s*([A-Z0-9\.\/]+)/i',
            '/Voyage\s*No\.?\s*[:\/]?\s*([A-Z0-9\.\/]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $voyage = trim($matches[1], " \t\n\r\0\x0B/");

                if ($voyage !== '' && preg_match('/\d/', $voyage) && str_contains($voyage, '/')) {
                    return $voyage;
                }
            }
        }

        return null;
    }

    private function extractDate(string $text, array $keywords): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $index => $line) {
            $lineLower = mb_strtolower($line);

            foreach ($keywords as $keyword) {
                if (str_contains($lineLower, $keyword)) {

                    if (preg_match('/\b(\d{1,2})[\-\/](\w{3,9}|\d{1,2})[\-\/](\d{4})\b/', $line, $matches)) {
                        return $this->normalizeDate($matches[1], $matches[2], $matches[3]);
                    }

                    if (isset($lines[$index + 1])) {
                        if (preg_match('/\b(\d{1,2})[\-\/](\w{3,9}|\d{1,2})[\-\/](\d{4})\b/', $lines[$index + 1], $matches)) {
                            return $this->normalizeDate($matches[1], $matches[2], $matches[3]);
                        }
                    }
                }
            }
        }

        return null;
    }

    private function normalizeDate(string $day, string $monthRaw, string $year): ?string
    {
        $months = [
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'may' => '05', 'mei' => '05', 'jun' => '06', 'jul' => '07',
            'aug' => '08', 'agu' => '08', 'sep' => '09', 'oct' => '10',
            'okt' => '10', 'nov' => '11', 'dec' => '12', 'des' => '12',
        ];

        $day = str_pad($day, 2, '0', STR_PAD_LEFT);

        if (is_numeric($monthRaw)) {
            $month = str_pad($monthRaw, 2, '0', STR_PAD_LEFT);
        } else {
            $monthKey = mb_strtolower(substr($monthRaw, 0, 3));
            $month = $months[$monthKey] ?? null;

            if ($month === null) {
                return null;
            }
        }

        return "{$year}-{$month}-{$day}";
    }

    private function extractBlLitersObs(string $text): ?float
    {
        $patterns = [
            '/Liters?\s*Observed[^\d]*([\d.,]+)/i',
            '/Bill\s*Of\s*Lading[^\d]*([\d.,]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseFlexibleNumber($matches[1]);
            }
        }

        return null;
    }

    private function extractLiters15C(string $text): ?float
    {
        $patterns = [
            '/Liters?\s*@?\s*15.{0,3}C[^\d]*?([\d.,]+)/iu',
            '/([\d.,]+)\s*Liter\s*15.{0,3}C/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseFlexibleNumber($matches[1]);
            }
        }

        return null;
    }

    private function parseFlexibleNumber(string $value): ?float
    {
        $value = trim($value);
        $value = rtrim($value, '.,');

        $cleaned = preg_replace('/[.,]/', '', $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
