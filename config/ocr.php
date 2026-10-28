<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Executable OCR
    |--------------------------------------------------------------------------
    |
    | Path ke tesseract.exe dan folder bin Poppler (berisi pdftoppm,
    | pdfseparate, pdfunite). Atur lewat .env kalau lokasinya berbeda.
    |
    */

    'tesseract_path' => env('TESSERACT_PATH', 'C:\Program Files\Tesseract-OCR\tesseract.exe'),

    'poppler_bin' => env('POPPLER_BIN', 'C:\poppler\Library\bin'),

    'languages' => ['eng', 'ind'],

    // Resolusi render halaman PDF; Tesseract paling akurat di sekitar 300 dpi
    'dpi' => (int) env('OCR_DPI', 300),

    // Pemrosesan gambar sebelum OCR (butuh ekstensi PHP GD):
    // gambar yang lebih sempit dari ini diperbesar, dan halaman diputar tegak
    // kalau keyakinan deteksi orientasi Tesseract minimal sebesar nilai ini
    'min_width' => 2000,

    // Di bawah 3 deteksi sering salah (halaman tegak dikira terbalik 180 derajat)
    'min_rotation_confidence' => 3.0,

    // Hasil OCR lebih pendek dari ini dianggap gagal, halaman dicoba lagi dalam posisi miring
    'min_text_length' => 50,

];
