<?php

/*
|--------------------------------------------------------------------------
| Aturan klasifikasi dokumen (rule based)
|--------------------------------------------------------------------------
|
| Setiap halaman diberi skor per kategori, kategori dengan skor tertinggi menang:
| - titles   : judul dokumen, dihitung sekali per halaman dengan bobot title_weight
| - keywords : dihitung setiap kali muncul (banyak "discharge" = dokumen bongkar)
| - patterns : regex pada teks yang sudah dinormalisasi, dihitung setiap kali muncul
|
| Kata kunci dicocokkan dari yang terpanjang, dan teks yang sudah cocok tidak
| dihitung lagi (mis. "loading order" tidak ikut menambah skor "loading").
| Kalau skor seri, kategori yang lebih atas di daftar ini yang menang.
|
*/

return [

    'title_weight' => 3,

    // Label yang muncul di semua jenis dokumen, dibuang sebelum dihitung
    'ignore' => [
        'port of loading',
        'port of discharg', // OCR sering memotong jadi "DISCHARG"
        'loading port',
        'discharge port',
        'loading master',
    ],

    'categories' => [

        'invoice' => [
            'label' => 'Invoice',
            'titles' => [
                'bill of lading',
                'manifest',
                'receipt for shipping document',
                'loading order',
            ],
            'keywords' => [],
            'patterns' => [],
        ],

        'bongkar' => [
            'label' => 'Dokumen Bongkar',
            'titles' => [
                'summary data',
                'certificate of quantity discharged',
            ],
            'keywords' => [
                'discharge',
                'discharging',
            ],
            'patterns' => [
                // Voyage dengan segmen D, mis. 022/D/P.G/PPN/ADO/V/2026
                '/\b\d+ ?\/ ?d ?\//',
            ],
        ],

        'loading' => [
            'label' => 'Dokumen Loading',
            'titles' => [
                'certificate of quantity loaded',
                'certificate of quantity loading',
            ],
            'keywords' => [
                'loading',
                'loaded',
            ],
            'patterns' => [
                // Voyage dengan segmen L, mis. 022/L/P.G/PPN/ADO/V/2026
                '/\b\d+ ?\/ ?l ?\//',
            ],
        ],

    ],

];
