<?php

namespace App\Services;

class DocumentFieldExtractorService
{
    public function extract(string $text): array
    {
        return [
            'vessel_name'     => $this->extractVesselName($text),
            'loading_date'    => $this->extractDate($text, [
                'loading commenced',
                'date of loading',
                'loading completed',
            ]),
            'discharge_date'  => $this->extractDate($text, [
                'discharge commenced',
                'discharging commenced',
                'discharge completed',
                'date of discharge',
            ]),
            'bl_liters_obs'   => $this->extractBlLitersObs($text),
            'liters_15c'      => $this->extractLiters15C($text),
        ];
    }

    private function extractVesselName(string $text): ?string
    {
        $patterns = [
            '/NAME OF VESSEL\s*[:\/]?\s*([A-Za-z0-9\.\-]+(?:\s+[A-Za-z0-9\.\-]+)*?)(?=\s+(?:PORT|NATIONALITY|VOYAGE|DATE|$))/i',
            '/Vessel\s*[:\/]?\s*([A-Za-z0-9\.\-]+(?:\s+[A-Za-z0-9\.\-]+)*?)(?=\s+(?:PORT|NATIONALITY|VOYAGE|DATE|Order|No\.|$))/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $name = trim($matches[1]);

                if ($name !== '' && mb_strlen($name) < 50) {
                    return $name;
                }
            }
        }

        return null;
    }

    private function extractDate(string $text, array $keywords): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {
            $lineLower = mb_strtolower($line);

            foreach ($keywords as $keyword) {
                if (str_contains($lineLower, $keyword)) {
                    if (preg_match('/\b(\d{1,2})[\-\/](\w{3,9}|\d{1,2})[\-\/](\d{4})\b/', $line, $matches)) {
                        return $this->normalizeDate($matches[1], $matches[2], $matches[3]);
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

    /**
     * Ambil "Liters Observed" sebagai BL liters obs.
     * OCR menghasilkan koma sebagai pemisah ribuan: "3,510,344"
     */
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

    /**
     * Ambil "Liters @ 15°C" / "Liter 15C" value.
     * OCR menghasilkan: "Liters @ 15°C Bl 3,445,842" atau "3,445,842 Liter 15C"
     */
    private function extractLiters15C(string $text): ?float
    {
        $patterns = [
            // Label sebelum angka: "Liters @ 15°C ... 3,445,842" -> cocokkan "15" + opsional simbol + "C"
            '/Liters?\s*@?\s*15.{0,3}C[^\d]*?([\d.,]+)/iu',
            // Angka sebelum label: "3,445,842 Liter 15C"
            '/([\d.,]+)\s*Liter\s*15.{0,3}C/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseFlexibleNumber($matches[1]);
            }
        }

        return null;
    }

    /**
     * Parse angka dengan format fleksibel:
     * - "3,510,344" (koma = ribuan, hasil OCR)
     * - "3.510.344" (titik = ribuan, PDF asli/Indonesia)
     * - "3.510,344" / "3,510.344" dengan desimal di akhir
     */
    private function parseFlexibleNumber(string $value): ?float
    {
        $value = trim($value);
        $value = rtrim($value, '.,');

        // Hapus semua separator ribuan (titik dan koma), asumsikan angka shipping ini integer besar
        $cleaned = preg_replace('/[.,]/', '', $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}