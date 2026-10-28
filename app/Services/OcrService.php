<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;

class OcrService
{
    /**
     * OCR sebuah file, hasilnya teks per halaman. Gambar (JPG/PNG) dianggap satu halaman.
     *
     * @return list<string>
     */
    public function pages(string $path): array
    {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf') {
            return [$this->image($path)];
        }

        // uniqid() mencegah bentrok kalau OCR dokumen yang sama jalan dua kali bersamaan
        $tempDir = storage_path('app/temp/ocr_'.uniqid());
        File::ensureDirectoryExists($tempDir);

        try {
            $result = Process::timeout(600)->run([
                $this->poppler('pdftoppm'),
                '-png',
                '-r', (string) config('ocr.dpi'),
                $path,
                $tempDir.DIRECTORY_SEPARATOR.'page',
            ]);

            if ($result->failed()) {
                throw new RuntimeException('Gagal merender PDF: '.$result->errorOutput());
            }

            $images = glob($tempDir.DIRECTORY_SEPARATOR.'page-*.png') ?: [];
            natsort($images);

            return array_values(array_map(fn (string $image) => $this->image($image), $images));
        } finally {
            // Folder sementara selalu dibersihkan, termasuk kalau OCR error di tengah jalan
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * Path executable Poppler, mis. pdftoppm atau pdfunite.
     */
    public function poppler(string $tool): string
    {
        $bin = config('ocr.poppler_bin');

        return $bin ? rtrim($bin, '\\/').DIRECTORY_SEPARATOR.$tool : $tool;
    }

    /**
     * psm 3 (segmentasi otomatis) dengan engine LSTM, setelah gambar diperbesar
     * dan diputar tegak (kalau ekstensi GD aktif).
     */
    private function image(string $imagePath): string
    {
        $prepared = $this->prepare($imagePath);

        try {
            $text = $this->recognize($prepared);

            // Teks kecil di halaman miring sering tidak terdeteksi orientasinya:
            // kalau hasilnya hampir kosong, coba posisi 90 dan 270 derajat, ambil teks terpanjang
            if ($prepared !== $imagePath && mb_strlen(trim($text)) < (int) config('ocr.min_text_length')) {
                foreach ([90, 270] as $angle) {
                    $rotated = $this->rotateFile($prepared, $angle);
                    $candidate = $this->recognize($rotated);
                    File::delete($rotated);

                    if (mb_strlen(trim($candidate)) > mb_strlen(trim($text))) {
                        $text = $candidate;
                    }
                }
            }

            return $text;
        } finally {
            if ($prepared !== $imagePath) {
                File::delete($prepared);
            }
        }
    }

    private function recognize(string $imagePath): string
    {
        try {
            return (new TesseractOCR($imagePath))
                ->executable(config('ocr.tesseract_path'))
                ->lang(...config('ocr.languages'))
                ->oem(1)
                ->psm(3)
                ->run();
        } catch (UnsuccessfulCommandException) {
            // Halaman kosong/tidak terbaca: Tesseract tidak mengeluarkan teks sama sekali
            return '';
        }
    }

    /**
     * Salinan gambar yang diputar searah jarum jam sebesar $angle derajat.
     */
    private function rotateFile(string $imagePath, int $angle): string
    {
        $image = imagecreatefrompng($imagePath);
        $rotated = imagerotate($image, 360 - $angle, 0xFFFFFF);
        $path = storage_path('app/temp/ocr_'.uniqid().'.png');

        imagepng($rotated, $path);
        imagedestroy($image);
        imagedestroy($rotated);

        return $path;
    }

    /**
     * Grayscale, perbesar gambar kecil, dan putar tegak sesuai deteksi orientasi Tesseract.
     * Tanpa GD, gambar dipakai apa adanya.
     */
    private function prepare(string $imagePath): string
    {
        if (! extension_loaded('gd')) {
            return $imagePath;
        }

        $image = @imagecreatefromstring((string) file_get_contents($imagePath));

        if ($image === false) {
            return $imagePath;
        }

        // Teks kecil sulit dibaca Tesseract, halaman diperbesar sampai lebar minimal.
        // Selalu lewat kanvas truecolor: gambar berpalet yang diskalakan langsung jadi berbayang.
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = max(1, (int) config('ocr.min_width') / $width);

        $canvas = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));
        imagefill($canvas, 0, 0, 0xFFFFFF);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, imagesx($canvas), imagesy($canvas), $width, $height);
        imagedestroy($image);
        $image = $canvas;

        imagefilter($image, IMG_FILTER_GRAYSCALE);

        $prepared = storage_path('app/temp/ocr_'.uniqid().'.png');
        File::ensureDirectoryExists(dirname($prepared));
        imagepng($image, $prepared);

        $rotate = $this->detectRotation($prepared);
        if ($rotate !== 0) {
            // Tesseract memberi derajat searah jarum jam, imagerotate berlawanan arah jarum jam
            $image = imagerotate($image, 360 - $rotate, 0xFFFFFF);
            imagepng($image, $prepared);
        }

        imagedestroy($image);

        return $prepared;
    }

    /**
     * Derajat putar (searah jarum jam) supaya halaman tegak, 0 kalau tidak terdeteksi.
     */
    private function detectRotation(string $imagePath): int
    {
        $result = Process::timeout(60)->run([
            config('ocr.tesseract_path'), $imagePath, 'stdout', '--psm', '0',
        ]);

        if (! preg_match('/Rotate:\s*(\d+)/', $result->output(), $rotate)
            || ! preg_match('/Orientation confidence:\s*([\d.]+)/', $result->output(), $confidence)) {
            return 0;
        }

        return (float) $confidence[1] >= (float) config('ocr.min_rotation_confidence')
            ? (int) $rotate[1] % 360
            : 0;
    }
}
