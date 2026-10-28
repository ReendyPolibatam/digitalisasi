<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class PdfPageService
{
    public function __construct(private OcrService $ocr) {}

    /**
     * Salin halaman tertentu (nomor mulai dari 1, urutan dipertahankan) ke PDF baru.
     *
     * @param  list<int>  $pageNumbers
     */
    public function extract(string $source, array $pageNumbers, string $destination): void
    {
        $tempDir = storage_path('app/temp/split_'.uniqid());
        File::ensureDirectoryExists($tempDir);

        try {
            $pageFiles = [];

            foreach ($pageNumbers as $page) {
                $pageFile = $tempDir.DIRECTORY_SEPARATOR."page-{$page}.pdf";

                $this->run([
                    $this->ocr->poppler('pdfseparate'),
                    '-f', (string) $page,
                    '-l', (string) $page,
                    $source,
                    $pageFile,
                ]);

                $pageFiles[] = $pageFile;
            }

            File::ensureDirectoryExists(dirname($destination));

            $this->run([$this->ocr->poppler('pdfunite'), ...$pageFiles, $destination]);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * @param  list<string>  $command
     */
    private function run(array $command): void
    {
        $result = Process::timeout(120)->run($command);

        if ($result->failed()) {
            throw new RuntimeException('Gagal memproses PDF: '.$result->errorOutput());
        }
    }
}
