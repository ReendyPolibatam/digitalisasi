<?php

use App\Services\DocumentClassifierService;
use App\Services\OcrService;

beforeEach(function () {
    $this->classifier = new DocumentClassifierService;
});

test('document titles classify invoice pages', function (string $text) {
    expect($this->classifier->classify($text)['category'])->toBe('invoice');
})->with([
    'bill of lading' => ['BILL OF LADING  Shipped on board ... commencement of loading ... there discharge'],
    'manifest' => ['MANIFEST  Name of vessel : OB Putra Gandus  Port of Loading : Sungai Pakning'],
    'receipt' => ['RECEIPT FOR SHIPPING DOCUMENTS  Lifting Date : May 31, 2026'],
    'loading order' => ['== LOADING ORDER ==  To load A.D.O  Loading A.D.O Ex. 946TK18  Est. Loading On 30 May'],
]);

test('the category with the most keyword occurrences wins', function () {
    $text = 'SUMMARY DATA  Bill Of Lading  At Loading  Loading Commenced
        At Discharge  Discharge Commenced  Discharge Completed  Discharging';

    $result = $this->classifier->classify($text);

    expect($result['category'])->toBe('bongkar')
        ->and($result['scores']['bongkar'])->toBeGreaterThan($result['scores']['loading'])
        ->and($result['scores']['bongkar'])->toBeGreaterThan($result['scores']['invoice']);
});

test('voyage segment D or L identifies discharge or loading', function () {
    expect($this->classifier->classify('VOYAGE : 022/D/P.G/PPN/ADO/V/2026')['category'])->toBe('bongkar')
        ->and($this->classifier->classify('VOYAGE : 022/L/P.G/PPN/ADO/V/2026')['category'])->toBe('loading');
});

test('labels shared by every document type are ignored', function () {
    $result = $this->classifier->classify('Port of Loading : Sungai Pakning  Port of Discharg : Depot Siak  Loading Master');

    expect($result['category'])->toBeNull()
        ->and(array_sum($result['scores']))->toBe(0);
});

test('a tie goes to invoice', function () {
    $result = $this->classifier->classify('Manifest  loading discharge loading discharge loading discharge');

    expect($result['scores'])->toBe(['invoice' => 3, 'bongkar' => 3, 'loading' => 3])
        ->and($result['category'])->toBe('invoice');
});

test('letter spaced titles are recognised', function () {
    expect($this->classifier->classify('M A N I F E S T')['category'])->toBe('invoice');
});

test('unrecognised pages follow the neighbouring page category', function () {
    $results = $this->classifier->classifyPages([
        'nothing useful here',
        'CERTIFICATE OF QUANTITY LOADED',
        'still nothing',
        'BILL OF LADING',
        '',
    ]);

    expect(array_column($results, 'category'))
        ->toBe(['loading', 'loading', 'loading', 'invoice', 'invoice']);
});

/**
 * OCR asli pada contoh dokumen di tests/Fixtures/ocr/{kategori}. Lambat (beberapa menit)
 * dan butuh Tesseract + ekstensi GD, jadi hanya jalan dengan RUN_OCR_TESTS=1.
 */
test('sample documents are classified into their folder category', function (string $category, string $file) {
    $text = app(OcrService::class)->pages($file)[0];

    expect($this->classifier->classify($text)['category'])->toBe($category);
})->with(function () {
    foreach (glob(dirname(__DIR__).'/Fixtures/ocr/*/*.png') as $file) {
        yield basename(dirname($file)).'/'.basename($file) => [basename(dirname($file)), $file];
    }
})->skip(fn () => ! env('RUN_OCR_TESTS'), 'Set RUN_OCR_TESTS=1 untuk menjalankan OCR asli');
